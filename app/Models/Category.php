<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'icon'
    ];

    public function getTranslatedNameAttribute(): string
    {
        $key = 'ui.' . Str::slug($this->name, '_');
        return trans()->has($key) ? __($key) : $this->name;
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
