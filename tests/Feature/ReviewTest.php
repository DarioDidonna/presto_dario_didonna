<?php

namespace Tests\Feature;

use App\Models\PurchaseRequest;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->request = PurchaseRequest::factory()->status(PurchaseRequest::ACCEPTED)->create();
    }

    private function review(User $user, array $data, ?PurchaseRequest $request = null)
    {
        return $this->actingAs($user)->post(route('reviews.store', $request ?? $this->request), $data);
    }

    public function test_buyer_reviews_the_seller_after_an_accepted_purchase(): void
    {
        $this->actingAs($this->request->buyer)->get(route('orders.index'))
            ->assertSee(__('ui.reviews_question', ['name' => $this->request->seller->name]))
            ->assertSee(route('reviews.store', $this->request));

        $this->review($this->request->buyer, ['rating' => 4, 'comment' => "  Puntuale e gentile.\nConsigliato!  "])
            ->assertRedirect(route('orders.index'))
            ->assertSessionHas('message', __('ui.reviews_thanks'));

        $review = Review::sole();
        $this->assertSame([4, "Puntuale e gentile.\nConsigliato!", $this->request->buyer_id, $this->request->seller_id],
            [$review->rating, $review->comment, $review->reviewer_id, $review->seller_id]);
        Notification::assertSentTo($this->request->seller, ReviewReceived::class);

        // Al posto del modulo compare la recensione, per l'acquirente e per il venditore
        $this->actingAs($this->request->buyer)->get(route('orders.index'))
            ->assertSee(__('ui.reviews_yours'))
            ->assertSee(__('ui.reviews_stars', ['rating' => 4, 'max' => 5]))
            ->assertDontSee(route('reviews.store', $this->request));
        $this->actingAs($this->request->seller)->get(route('sales.index'))
            ->assertSee(__('ui.reviews_from', ['name' => $this->request->buyer->name]))
            ->assertSee('Puntuale e gentile.');
    }

    public function test_only_the_buyer_of_an_accepted_request_can_review_once(): void
    {
        $this->post(route('reviews.store', $this->request), ['rating' => 5])->assertRedirect(route('login'));
        $this->review($this->request->seller, ['rating' => 5])->assertForbidden();
        $this->review(User::factory()->create(), ['rating' => 5])->assertForbidden();

        foreach ([PurchaseRequest::PENDING, PurchaseRequest::DECLINED, PurchaseRequest::CANCELLED] as $status) {
            $request = PurchaseRequest::factory()->status($status)->create();
            $this->review($request->buyer, ['rating' => 5], $request)->assertForbidden();
            $this->actingAs($request->buyer)->get(route('orders.index'))->assertDontSee(route('reviews.store', $request));
        }

        $this->review($this->request->buyer, ['rating' => 5])->assertRedirect();
        $this->review($this->request->buyer, ['rating' => 1])->assertForbidden();

        $this->assertSame(5, Review::sole()->rating);
    }

    public function test_validation_errors_show_only_under_the_submitted_form(): void
    {
        // Un secondo ordine da recensire: due moduli nella stessa pagina
        PurchaseRequest::factory()->status(PurchaseRequest::ACCEPTED)->create(['buyer_id' => $this->request->buyer_id]);
        $bag = 'review'.$this->request->id;

        foreach ([[], ['rating' => 0], ['rating' => 6], ['rating' => 'tante']] as $data) {
            $this->review($this->request->buyer, $data)->assertSessionHasErrors('rating', errorBag: $bag);
        }
        $this->review($this->request->buyer, ['rating' => 3, 'comment' => str_repeat('a', 1001)])->assertSessionHasErrors('comment', errorBag: $bag);

        $this->from(route('orders.index'))->review($this->request->buyer, ['rating' => 9, 'comment' => 'Testo da non perdere'])->assertRedirect(route('orders.index'));
        $page = $this->actingAs($this->request->buyer)->get(route('orders.index'));
        $page->assertSee(__('ui.reviews_rating_required'))->assertSee('Testo da non perdere');
        $this->assertSame(1, substr_count($page->getContent(), __('ui.reviews_rating_required')), 'errore solo sotto il modulo inviato');
        $this->assertSame(1, substr_count($page->getContent(), 'Testo da non perdere'));

        $this->assertSame(0, Review::count());
    }

    public function test_empty_comment_is_stored_as_null_and_comments_are_escaped(): void
    {
        $this->review($this->request->buyer, ['rating' => 2, 'comment' => '   ']);
        $this->assertNull(Review::sole()->comment);

        $other = PurchaseRequest::factory()->status(PurchaseRequest::ACCEPTED)->create(['seller_id' => $this->request->seller_id]);
        $this->review($other->buyer, ['rating' => 1, 'comment' => '<script>alert(1)</script>'], $other);

        $this->actingAs($this->request->seller)->get(route('sales.index'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_review_email_for_the_seller(): void
    {
        $review = Review::factory()->create(['purchase_request_id' => $this->request->id, 'rating' => 5, 'comment' => 'Ottimo']);
        $mail = (new ReviewReceived($review))->toMail($this->request->seller);

        $this->assertSame(__('ui.reviews_mail_subject', ['name' => $this->request->buyer->name]), $mail->subject);
        $this->assertContains('«Ottimo»', $mail->introLines);
        $this->assertSame(route('sales.index'), $mail->actionUrl);
    }
}
