<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use App\Models\Category;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $asesores = User::role('Asesor Inmobiliario')->get();
        $categorias = Category::all();

        if ($asesores->isEmpty() || $categorias->isEmpty()) {
            $this->command->info('Ejecuta primero RolePermissionSeeder y CategorySeeder');
            return;
        }

        $properties = [
            [
                'title' => 'Apartamento de Lujo en Altamira',
                'description' => 'Hermoso apartamento en la mejor zona de Altamira. Con acabados de lujo, vista panorámica y cerca de centros comerciales.',
                'price' => 250000,
                'location' => 'Altamira, Caracas',
                'address' => 'Av. Principal de Altamira, Edf. Altamira Palace',
                'sector' => 'Altamira',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'parking_spaces' => 2,
                'area' => 120,
                'type' => 'venta',
                'status' => 'publicada',
                'is_featured' => true,
                'features' => [
                    'amenidades' => ['Piscina', 'Gimnasio', 'Salón de fiestas', 'Seguridad 24h'],
                    'pisos' => 'Parquet',
                    'vista' => 'Panorámica',
                    'cocina' => 'Integrada con electrodomésticos'
                ],
            ],
            [
                'title' => 'Casa con Jardín en La Lagunita',
                'description' => 'Espaciosa casa con hermoso jardín, ideal para familia. Ubicada en una de las urbanizaciones más exclusivas de Caracas.',
                'price' => 450000,
                'location' => 'La Lagunita, Caracas',
                'address' => 'Calle Los Samanes, Quinta Mariana',
                'sector' => 'La Lagunita',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'parking_spaces' => 3,
                'area' => 280,
                'land_area' => 450,
                'type' => 'venta',
                'status' => 'publicada',
                'is_featured' => true,
                'features' => [
                    'amenidades' => ['Jardín', 'Churuata', 'Piscina privada'],
                    'piso' => 'Cerámica',
                    'cocina' => 'Isla central',
                    'closet' => 'Empotrados'
                ],
            ],
            [
                'title' => 'Local Comercial en Chacao',
                'description' => 'Excelente local comercial en pleno corazón de Chacao. Alta afluencia de público, ideal para negocio.',
                'price' => 1800,
                'location' => 'Chacao, Caracas',
                'address' => 'Calle Vargas, CC Chacao, Local 12',
                'sector' => 'Chacao',
                'area' => 85,
                'type' => 'alquiler',
                'status' => 'publicada',
                'is_featured' => false,
                'features' => [
                    'baño_privado' => true,
                    'depósito' => true,
                    'vitrina' => 'Cristal templado',
                    'aire_acondicionado' => true
                ],
            ],
            [
                'title' => 'Apartamento en El Rosal',
                'description' => 'Cómodo apartamento cerca de todo. Ideal para profesionales o parejas.',
                'price' => 850,
                'location' => 'El Rosal, Caracas',
                'address' => 'Av. Francisco de Miranda, Edf. Rosal Center',
                'sector' => 'El Rosal',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'parking_spaces' => 1,
                'area' => 75,
                'type' => 'alquiler',
                'status' => 'publicada',
                'is_featured' => false,
                'features' => [
                    'amenidades' => ['Seguridad', 'Ascensor'],
                    'piso' => 'Cerámica',
                    'cocina' => 'Empotrada'
                ],
            ],
            [
                'title' => 'Terreno en Baruta',
                'description' => 'Terreno con hermosa vista, ideal para construcción de casa o edificio. Todos los servicios disponibles.',
                'price' => 120000,
                'location' => 'Baruta, Caracas',
                'address' => 'Vía principal de Baruta, Sector La Trinidad',
                'sector' => 'La Trinidad',
                'land_area' => 500,
                'type' => 'venta',
                'status' => 'publicada',
                'is_featured' => false,
                'features' => [
                    'topografía' => 'Plana',
                    'servicios' => ['Agua', 'Luz', 'Teléfono', 'Internet'],
                    'acceso' => 'Pavimentado'
                ],
            ],
            [
                'title' => 'Oficina en Las Mercedes',
                'description' => 'Oficina moderna en el centro financiero de Las Mercedes. Ideal para empresa.',
                'price' => 1200,
                'location' => 'Las Mercedes, Caracas',
                'address' => 'Calle París, Torre Ejecutiva, Piso 5',
                'sector' => 'Las Mercedes',
                'area' => 120,
                'type' => 'alquiler',
                'status' => 'publicada',
                'is_featured' => true,
                'features' => [
                    'puestos' => 8,
                    'sala_reuniones' => true,
                    'cocina' => true,
                    'baños' => 2,
                    'aire_acondicionado' => 'Central'
                ],
            ],
        ];

        foreach ($properties as $index => $propertyData) {
            // Asignar asesor rotativamente
            $asesor = $asesores[$index % count($asesores)];

            // Asignar categoría según el título
            if (str_contains($propertyData['title'], 'Apartamento')) {
                $category = $categorias->where('name', 'Apartamento')->first();
            } elseif (str_contains($propertyData['title'], 'Casa')) {
                $category = $categorias->where('name', 'Casa')->first();
            } elseif (str_contains($propertyData['title'], 'Local')) {
                $category = $categorias->where('name', 'Local Comercial')->first();
            } elseif (str_contains($propertyData['title'], 'Terreno')) {
                $category = $categorias->where('name', 'Terreno')->first();
            } elseif (str_contains($propertyData['title'], 'Oficina')) {
                $category = $categorias->where('name', 'Oficina')->first();
            } else {
                $category = $categorias->first();
            }

            $property = Property::create(array_merge($propertyData, [
                'user_id' => $asesor->id,
                'category_id' => $category->id,
                'city' => 'Caracas',
                'state' => 'Distrito Capital',
                'country' => 'Venezuela',
            ]));

            // Crear imagen principal de ejemplo
            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => 'properties/example-' . ($index + 1) . '.jpg',
                'is_primary' => true,
                'order' => 0,
            ]);
        }
    }
}
