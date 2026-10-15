<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\CategoriesSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([CategoriesSeeder::class]);

        // Utenti e annunci dimostrativi solo in locale (niente WithoutModelEvents: servono a Scout per indicizzare)
        if (app()->environment('local')) {
            $this->call([DemoSeeder::class]);
        }
    }
}
