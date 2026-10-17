<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Livewire\Conversation;
use App\Models\Message;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = PurchaseRequest::factory()->create();
    }

    public function test_only_buyer_and_seller_can_open_the_conversation(): void
    {
        $url = route('conversations.show', $this->request);

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($this->request->buyer)->get($url)->assertOk()->assertSee(__('ui.messages_empty'));
        $this->actingAs($this->request->seller)->get($url)->assertOk()->assertSeeLivewire(Conversation::class);
    }

    public function test_buyer_and_seller_exchange_messages(): void
    {
        Livewire::actingAs($this->request->buyer)->test(Conversation::class, ['purchaseRequest' => $this->request])
            ->set('body', "  Posso passare sabato?\nVerso le 10  ")
            ->call('send')
            ->assertHasNoErrors()
            ->assertSet('body', '')
            ->assertDispatched('conversation-updated');

        Livewire::actingAs($this->request->seller)->test(Conversation::class, ['purchaseRequest' => $this->request])
            ->assertSee("Posso passare sabato?\nVerso le 10")
            ->set('body', 'Perfetto, ti aspetto')
            ->call('send');

        $this->assertSame(
            [[$this->request->buyer_id, "Posso passare sabato?\nVerso le 10"], [$this->request->seller_id, 'Perfetto, ti aspetto']],
            $this->request->messages()->get()->map(fn ($m) => [$m->user_id, $m->body])->all(),
        );
    }

    public function test_strangers_cannot_read_or_write(): void
    {
        Message::factory()->create(['purchase_request_id' => $this->request->id, 'user_id' => $this->request->buyer_id, 'body' => 'Segreto']);

        Livewire::actingAs(User::factory()->create())->test(Conversation::class, ['purchaseRequest' => $this->request])
            ->assertForbidden();

        $this->assertSame(1, Message::count());
    }

    public function test_purchase_request_id_cannot_be_tampered_with(): void
    {
        $component = Livewire::actingAs($this->request->buyer)->test(Conversation::class, ['purchaseRequest' => $this->request]);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('purchaseRequestId', PurchaseRequest::factory()->create()->id);
    }

    public function test_declined_or_cancelled_requests_are_read_only(): void
    {
        foreach ([PurchaseRequest::DECLINED, PurchaseRequest::CANCELLED] as $status) {
            $request = PurchaseRequest::factory()->status($status)->create();

            $this->actingAs($request->buyer)->get(route('conversations.show', $request))
                ->assertOk()
                ->assertSee(__('ui.messages_closed_'.$status))
                ->assertDontSee(__('ui.messages_placeholder'));

            Livewire::actingAs($request->seller)->test(Conversation::class, ['purchaseRequest' => $request])
                ->set('body', 'ciao')->call('send')->assertForbidden();
        }

        // Una richiesta accettata resta aperta: bisogna ancora accordarsi sul ritiro
        $accepted = PurchaseRequest::factory()->status(PurchaseRequest::ACCEPTED)->create();
        Livewire::actingAs($accepted->buyer)->test(Conversation::class, ['purchaseRequest' => $accepted])
            ->set('body', 'Quando passo?')->call('send')->assertHasNoErrors();

        $this->assertSame(1, Message::count());
    }

    public function test_validation_and_rate_limit(): void
    {
        $component = Livewire::actingAs($this->request->buyer)->test(Conversation::class, ['purchaseRequest' => $this->request]);

        $component->set('body', '   ')->call('send')->assertHasErrors(['body' => 'required']);
        $component->set('body', str_repeat('a', 1001))->call('send')->assertHasErrors(['body' => 'max']);

        RateLimiter::clear('conversation:'.$this->request->buyer_id);
        for ($i = 0; $i < Conversation::MAX_MESSAGES_PER_MINUTE; $i++) {
            $component->set('body', "messaggio $i")->call('send')->assertHasNoErrors();
        }
        $component->set('body', 'uno di troppo')->call('send')->assertHasErrors('body');

        $this->assertSame(Conversation::MAX_MESSAGES_PER_MINUTE, Message::count());
    }

    public function test_opening_the_conversation_marks_the_other_persons_messages_as_read(): void
    {
        $fromBuyer = Message::factory()->create(['purchase_request_id' => $this->request->id, 'user_id' => $this->request->buyer_id]);
        $fromSeller = Message::factory()->create(['purchase_request_id' => $this->request->id, 'user_id' => $this->request->seller_id]);

        $this->actingAs($this->request->seller)->get(route('conversations.show', $this->request))->assertOk();

        $this->assertNotNull($fromBuyer->fresh()->read_at);
        $this->assertNull($fromSeller->fresh()->read_at);
    }

    public function test_messages_are_escaped(): void
    {
        Message::factory()->create(['purchase_request_id' => $this->request->id, 'user_id' => $this->request->buyer_id, 'body' => '<script>alert(1)</script>']);

        $this->actingAs($this->request->seller)->get(route('conversations.show', $this->request))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_orders_and_sales_pages_link_to_the_conversation(): void
    {
        $link = route('conversations.show', $this->request);

        $this->actingAs($this->request->buyer)->get(route('orders.index'))->assertSee($link);
        $this->actingAs($this->request->seller)->get(route('sales.index'))->assertSee($link);
    }

    public function test_a_new_message_is_broadcast_on_the_private_channel_of_the_conversation(): void
    {
        Event::fake([MessageSent::class]);

        Livewire::actingAs($this->request->buyer)->test(Conversation::class, ['purchaseRequest' => $this->request])
            ->set('body', 'Ciao')->call('send');

        Event::assertDispatched(MessageSent::class, function (MessageSent $event) {
            // Chat aperta della conversazione + canale personale del destinatario (il venditore)
            return array_column($event->broadcastOn(), 'name') === [
                'private-conversations.'.$this->request->id,
                'private-App.Models.User.'.$this->request->seller_id,
            ]
                // Mai il testo: solo dove aggiornare i contatori
                && $event->broadcastWith() === ['id' => $event->message->id, 'purchase_request_id' => $this->request->id, 'box' => 'sales'];
        });
    }

    public function test_message_is_saved_even_if_reverb_is_down(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.options' => ['host' => '127.0.0.1', 'port' => 1, 'scheme' => 'http', 'useTLS' => false],
        ]);

        Livewire::actingAs($this->request->buyer)->test(Conversation::class, ['purchaseRequest' => $this->request])
            ->set('body', 'Ci sei?')->call('send')->assertHasNoErrors();

        $this->assertSame('Ci sei?', Message::sole()->body);
    }

    public function test_only_buyer_and_seller_can_listen_to_the_channel(): void
    {
        $canListen = Broadcast::getChannels()['conversations.{purchaseRequest}'];

        $this->assertTrue($canListen($this->request->buyer, $this->request));
        $this->assertTrue($canListen($this->request->seller, $this->request));
        $this->assertFalse($canListen(User::factory()->create(), $this->request));
    }

    public function test_an_incoming_message_is_marked_as_read_while_the_conversation_is_open(): void
    {
        $component = Livewire::actingAs($this->request->seller)->test(Conversation::class, ['purchaseRequest' => $this->request]);
        $message = Message::factory()->create(['purchase_request_id' => $this->request->id, 'user_id' => $this->request->buyer_id, 'body' => 'Appena arrivato']);

        $component->call('messageReceived')->assertSee('Appena arrivato')->assertDispatched('conversation-updated');

        $this->assertNotNull($message->fresh()->read_at);
    }

    private function message(User $from, string $body = 'ciao', ?PurchaseRequest $request = null): Message
    {
        $request ??= $this->request;

        return Message::factory()->create(['purchase_request_id' => $request->id, 'user_id' => $from->id, 'body' => $body]);
    }

    public function test_unread_counts_are_split_between_orders_and_sales(): void
    {
        $user = $this->request->buyer;
        $sale = PurchaseRequest::factory()->create(['seller_id' => $user->id]);

        $this->message($this->request->seller);
        $this->message($this->request->seller);
        $this->message($user); // scritto da me: non conta
        $this->message($sale->buyer, request: $sale);
        $this->message($sale->buyer, request: $sale)->forceFill(['read_at' => now()])->save();
        $other = PurchaseRequest::factory()->create();
        $this->message($other->buyer, request: $other); // conversazione altrui

        $this->assertSame(['orders' => 2, 'sales' => 1], $user->unreadMessageCounts());
        $this->assertSame(['orders' => 0, 'sales' => 0], User::factory()->create()->unreadMessageCounts());
    }

    public function test_navbar_and_cards_show_unread_messages_until_the_conversation_is_opened(): void
    {
        $buyer = $this->request->buyer;
        $this->message($this->request->seller);
        $this->message($this->request->seller);

        $this->actingAs($buyer)->get(route('orders.index'))
            ->assertSeeInOrder(['data-unread-badge="orders"', 'data-unread-count>2<'], false)
            ->assertSeeInOrder([__('ui.messages_open'), '2<span class="visually-hidden"> '.__('ui.messages_unread')], false);

        $this->actingAs($buyer)->get(route('conversations.show', $this->request))->assertOk();

        $this->actingAs($buyer)->get(route('orders.index'))
            ->assertSee('data-unread-count>0<', false)
            ->assertDontSee('2<span class="visually-hidden"> '.__('ui.messages_unread'), false);
    }

    public function test_an_email_is_scheduled_for_the_recipient_after_a_delay(): void
    {
        Notification::fake();

        Livewire::actingAs($this->request->buyer)->test(Conversation::class, ['purchaseRequest' => $this->request])
            ->set('body', 'Ci sei?')->call('send');

        Notification::assertSentTo($this->request->seller, NewMessage::class, function (NewMessage $notification) {
            return $notification->delay->diffInMinutes(now(), true) >= Conversation::EMAIL_DELAY_MINUTES - 0.1;
        });
        Notification::assertNotSentTo($this->request->buyer, NewMessage::class);
    }

    public function test_the_email_is_skipped_if_the_message_was_read_or_another_email_is_already_on_its_way(): void
    {
        $seller = $this->request->seller;
        $first = $this->message($this->request->buyer, 'Ciao');
        $second = $this->message($this->request->buyer, 'Ci sei?');

        // Una sola email per raffica: quella del primo messaggio non letto
        $this->assertTrue((new NewMessage($first))->shouldSend($seller, 'mail'));
        $this->assertFalse((new NewMessage($second))->shouldSend($seller, 'mail'));

        // Letti prima dello scadere del ritardo: nessuna email
        $this->actingAs($seller)->get(route('conversations.show', $this->request));
        $this->assertFalse((new NewMessage($first->fresh()))->shouldSend($seller, 'mail'));
    }

    public function test_email_content_links_to_the_conversation(): void
    {
        $mail = (new NewMessage($this->message($this->request->buyer, 'Posso passare <b>sabato</b>?')))->toMail($this->request->seller);

        $this->assertSame(route('conversations.show', $this->request), $mail->actionUrl);
        $this->assertSame(__('ui.messages_mail_subject', ['name' => $this->request->buyer->name]), $mail->subject);
        $this->assertStringContainsString('Posso passare <b>sabato</b>?', implode(' ', $mail->introLines)); // escape nel template dell'email
    }
}
