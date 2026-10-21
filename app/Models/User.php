<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property-read float|null $reviews_received_avg_rating con withAvg() (pagina dell'annuncio)
 * @property-read int|null $reviews_received_count con withCount()
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return HasMany<Article, $this> */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    // Annunci nel carrello (tabella cart_items), dal più recente
    /** @return BelongsToMany<Article, $this> */
    public function cart(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'cart_items')->withTimestamps()->latest('cart_items.created_at');
    }

    // Lingua delle notifiche email: quella scelta dall'utente, altrimenti l'italiano come il sito
    public function preferredLocale(): string
    {
        return in_array($this->locale, config('app.available_locales'), true) ? $this->locale : 'it';
    }

    /** @return HasMany<PurchaseRequest, $this> */
    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'buyer_id');
    }

    /** @return HasMany<PurchaseRequest, $this> */
    public function receivedPurchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'seller_id');
    }

    // Recensioni ricevute come venditore
    /** @return HasMany<Review, $this> */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'seller_id');
    }

    /**
     * Media (arrotondata a un decimale, null senza recensioni) e numero delle recensioni ricevute.
     * Usa i valori di withAvg/withCount se già caricati, altrimenti una query.
     *
     * @return array{average: ?float, count: int}
     */
    public function reviewSummary(): array
    {
        if (array_key_exists('reviews_received_count', $this->attributes)) {
            [$average, $count] = [$this->reviews_received_avg_rating, $this->reviews_received_count];
        } else {
            $row = $this->reviewsReceived()->toBase()->selectRaw('avg(rating) as average, count(*) as total')->first();
            [$average, $count] = [$row?->average, $row?->total];
        }

        return ['average' => $count ? round((float) $average, 1) : null, 'count' => (int) $count];
    }

    /**
     * Messaggi non letti scritti dall'altra persona, divisi tra ordini (sono l'acquirente) e vendite
     * (sono il venditore). Una sola query: la navbar la esegue a ogni pagina.
     *
     * @return array{orders: int, sales: int}
     */
    public function unreadMessageCounts(): array
    {
        $counts = Message::query()
            ->join('purchase_requests', 'purchase_requests.id', '=', 'messages.purchase_request_id')
            ->where('messages.user_id', '!=', $this->id)
            ->whereNull('messages.read_at')
            ->where(fn ($query) => $query->where('purchase_requests.buyer_id', $this->id)->orWhere('purchase_requests.seller_id', $this->id))
            ->selectRaw('sum(case when purchase_requests.buyer_id = ? then 1 else 0 end) as orders', [$this->id])
            ->selectRaw('sum(case when purchase_requests.seller_id = ? then 1 else 0 end) as sales', [$this->id])
            ->toBase()
            ->first();

        return ['orders' => (int) $counts?->orders, 'sales' => (int) $counts?->sales];
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : null;
    }

    // Iniziali di nome e cognome, mostrate quando non c'è una foto profilo
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
