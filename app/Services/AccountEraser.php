<?php

namespace App\Services;

use App\Models\Article;
use App\Models\PurchaseRequest;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Cancellazione dell'account su richiesta dell'utente (GDPR). Elimina i suoi dati personali e tiene una riga anonima:
 * una cancellazione vera, per le chiavi esterne, toglierebbe anche gli ordini e le vendite dell'altra persona.
 */
class AccountEraser
{
    public function __construct(private PurchaseRequestWorkflow $workflow) {}

    public function erase(User $user): void
    {
        $this->closePendingRequests($user);

        DB::transaction(function () use ($user) {
            $user->cart()->detach();
            $user->articles()->each(fn (Article $article) => $article->deleteWithFiles());

            // Quello che ha scritto può contenere dati personali: restano solo il voto e gli importi
            DB::table('messages')->where('user_id', $user->id)->delete();
            PurchaseRequest::where('buyer_id', $user->id)->update(['message' => null]);
            Review::where('reviewer_id', $user->id)->update(['comment' => null]);

            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }

            $user->forceFill([
                'name' => 'Utente eliminato',
                'email' => "deleted-{$user->id}@deleted.invalid",
                'password' => Str::random(64), // Nessuno la conosce: al login non si entra più
                'remember_token' => null,
                'email_verified_at' => null,
                'avatar' => null,
                'locale' => null,
                'is_revisor' => false,
                'anonymized_at' => now(),
            ])->save();
        });

        Storage::disk('public')->deleteDirectory("avatars/{$user->id}");
    }

    // Altrimenti gli annunci di altri resterebbero riservati per sempre, e l'altra persona non saprebbe nulla
    private function closePendingRequests(User $user): void
    {
        $user->purchaseRequests()->where('status', PurchaseRequest::PENDING)->get()
            ->each(fn (PurchaseRequest $request) => $this->close(fn () => $this->workflow->cancel($request)));

        $user->receivedPurchaseRequests()->where('status', PurchaseRequest::PENDING)->get()
            ->each(fn (PurchaseRequest $request) => $this->close(fn () => $this->workflow->decline($request)));
    }

    private function close(callable $transition): void
    {
        try {
            $transition();
        } catch (PurchaseRequestNotPendingException) {
            // L'altra persona l'ha appena chiusa: non c'è più niente da fare
        }
    }
}
