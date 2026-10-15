<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Di default l'annuncio è in attesa di revisione (is_accepted = null), come appena creato dal form.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 1, 1000),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'is_accepted' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(['is_accepted' => true]);
    }

    public function rejected(): static
    {
        return $this->state(['is_accepted' => false]);
    }
}
