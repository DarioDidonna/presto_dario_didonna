<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'description',
        'price',
        'category_id',
        'user_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
            'reserved_at' => 'datetime',
            'sold_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    // Acquistabile: pubblicato e non riservato da una richiesta d'acquisto in attesa
    public function isAvailable(): bool
    {
        return $this->is_accepted === true && $this->reserved_at === null && $this->sold_at === null;
    }

    public function isSold(): bool
    {
        return $this->sold_at !== null;
    }

    public function isReserved(): bool
    {
        return $this->reserved_at !== null;
    }

    public function setAccepted($value)
    {
        $this->is_accepted = $value;
        $this->save();

        return true;
    }

    // Elenchi pubblici (ricerca, categorie, homepage, statistiche, agente AI): pubblicati e non ancora venduti.
    // La pagina di un annuncio venduto resta raggiungibile (ArticlePolicy::view), con l'etichetta "Venduto".
    /**
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('is_accepted', true)->whereNull('sold_at');
    }

    /**
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    public function scopeWithCardRelations(Builder $query): Builder
    {
        return $query->with(['images', 'category']);
    }

    // Annunci in attesa che questo revisore può giudicare (non i propri: ArticlePolicy::review)
    /**
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    public function scopeToReviewBy(Builder $query, User $reviewer): Builder
    {
        return $query->whereNull('is_accepted')
            ->where(fn ($owner) => $owner->whereNull('user_id')->orWhere('user_id', '!=', $reviewer->id));
    }

    public static function toBeRevisedCount()
    {
        // once(): la navbar può chiamarlo più volte nella stessa richiesta
        return once(fn () => Article::whereNull('is_accepted')->count());
    }

    // scout:import carica le categorie in blocco invece di una query per articolo
    protected function makeAllSearchableUsing($query)
    {
        return $query->with('category');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category?->name,
        ];
    }

    /** @return HasMany<Image, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
