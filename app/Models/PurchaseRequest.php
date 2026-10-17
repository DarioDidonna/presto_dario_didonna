<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest()->orderBy('id');
    }

    // Acquirente o venditore: le uniche due persone che vedono la richiesta e i suoi messaggi
    public function involves(User $user): bool
    {
        return in_array($user->id, [$this->buyer_id, $this->seller_id], true);
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
