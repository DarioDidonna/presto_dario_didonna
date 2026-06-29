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
                'icon' => 'fa fa-shopping-bag'
            ],
            [
                'name' => 'Accessori', 
                'icon' => 'bi bi-device-ssd'
            ],
            [
                'name' => 'Animali domestici', 
                'icon' => 'fa fa-leaf'
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

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'icon' => $category['icon'],
            ]);
        }
    }
}