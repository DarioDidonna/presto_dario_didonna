<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Gli annunci non pubblicati (in revisione o rifiutati) sono visibili solo all'autore e ai revisori.
     */
    public function view(?User $user, Article $article): bool
    {
        return $article->is_accepted === true
            || ($user && ($user->is_revisor || $user->id === $article->user_id));
    }

    // Carrello: solo annunci pubblicati, non riservati e non propri
    public function addToCart(User $user, Article $article): bool
    {
        return $article->isAvailable() && $user->id !== $article->user_id;
    }

    public function update(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }
}
