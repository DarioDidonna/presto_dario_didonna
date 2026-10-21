<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseRequest extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const ACCEPTED = 'accepted';

    public const DECLINED = 'declined';

    public const CANCELLED = 'cancelled';

    protected $fillable = ['buyer_id', 'seller_id', 'status', 'message', 'total'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    /** @return BelongsTo<User, $this> */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /** @return HasMany<PurchaseRequestItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest()->orderBy('id');
    }

    /** @return HasOne<Review, $this> */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // Acquirente o venditore: le uniche due persone che vedono la richiesta e i suoi messaggi
    public function involves(User $user): bool
    {
        return in_array($user->id, [$this->buyer_id, $this->seller_id], true);
    }

    // L'altra persona della trattativa
    public function counterpart(User $user): ?User
    {
        return $user->id === $this->buyer_id ? $this->seller : $this->buyer;
    }

    // Per le schede di ordini e vendite: quanti messaggi dell'altra persona l'utente non ha ancora letto
    public function scopeWithUnreadMessagesCount(Builder $query, User $user): void
    {
        $query->withCount(['messages as unread_messages_count' => fn ($messages) => $messages
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')]);
    }

    // Si scrive finché la trattativa è aperta o conclusa con successo; rifiutata o annullata, resta in sola lettura
    public function acceptsMessages(): bool
    {
        return in_array($this->status, [self::PENDING, self::ACCEPTED], true);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
