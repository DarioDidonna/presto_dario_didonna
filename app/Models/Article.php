<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
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
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function setAccepted($value)
    {
        $this->is_accepted = $value;
        $this->save();
        return true;
    }

    public function scopeAccepted($query)
    {
        return $query->where('is_accepted', true);
    }

    public function scopeWithCardRelations($query)
    {
        return $query->with(['images', 'category']);
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
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'category'    => $this->category?->name,
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
