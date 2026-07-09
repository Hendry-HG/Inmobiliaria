<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Apartamento',
                'slug' => 'apartamento',
                'description' => 'Apartamentos residenciales en edificios',
                'icon' => 'building',
                'order' => 1,
            ],
            [
                'name' => 'Casa',
                'slug' => 'casa',
                'description' => 'Casas independientes y quintas',
                'icon' => 'home',
                'order' => 2,
            ],
            [
                'name' => 'Local Comercial',
                'slug' => 'local-comercial',
                'description' => 'Locales para negocios y comercios',
                'icon' => 'store',
                'order' => 3,
            ],
            [
                'name' => 'Oficina',
                'slug' => 'oficina',
                'description' => 'Oficinas profesionales y corporativas',
                'icon' => 'office-building',
                'order' => 4,
            ],
            [
                'name' => 'Terreno',
                'slug' => 'terreno',
                'description' => 'Terrenos y parcelas para construcción',
                'icon' => 'terrain',
                'order' => 5,
            ],
            [
                'name' => 'Galpón',
                'slug' => 'galpon',
                'description' => 'Galpones industriales y depósitos',
                'icon' => 'warehouse',
                'order' => 6,
            ],
            [
                'name' => 'Quinta',
                'slug' => 'quinta',
                'description' => 'Quintas y casas de lujo con amplios espacios',
                'icon' => 'villa',
                'order' => 7,
            ],
            [
                'name' => 'Habitación',
                'slug' => 'habitacion',
                'description' => 'Habitaciones en alquiler para estudiantes o profesionales',
                'icon' => 'bed',
                'order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
