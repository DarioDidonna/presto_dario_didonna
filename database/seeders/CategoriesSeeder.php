<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Abbigliamento', 
                'icon' => 'bi bi-bag'
            ],
            [
                'name' => 'Accessori', 
                'icon' => 'bi bi-device-ssd'
            ],
            [
                'name' => 'Animali domestici', 
                'icon' => 'bi bi-house-heart'
            ],
            [
                'name' => 'Casa e Giardinaggio', 
                'icon' => 'bi bi-house-door'
            ],
            [
                'name' => 'Elettronica', 
                'icon' => 'bi bi-laptop'
            ],
            [
                'name' => 'Giocattoli', 
                'icon' => 'bi bi-controller'
            ],
            [
                'name' => 'Libri e Riviste', 
                'icon' => 'bi bi-book'
            ],
            [
                'name' => 'Motori', 
                'icon' => 'bi bi-car-front'
            ],
            [
                'name' => 'Salute e Bellezza', 
                'icon' => 'bi bi-lungs-fill'
            ],
            [
                'name' => 'Sport', 
                'icon' => 'bi bi-dribbble'
            ]
        ];

        // Rieseguibile: aggiorna le categorie esistenti invece di duplicarle
        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], ['icon' => $category['icon']]);
        }
    }
}