<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Venezuela
        $venezuela = Country::firstOrCreate(
            ['name' => 'Venezuela'],
            ['code' => 'VEN']
        );

        // 2. Estados de Venezuela
        $estados = [
            'Distrito Capital' => [],
            'Miranda' => [
                'Sucre' => [],
                'Baruta' => [
                    'Baruta Norte' => ['Baruta', 'Las Minas'],
                    'Baruta Sur' => ['Santa Cruz', 'La Trinidad']
                ],
                'Chacao' => ['Chacao'],
                'El Hatillo' => ['El Hatillo']
            ],
            'Zulia' => [
                'Maracaibo' => ['Maracaibo', 'Bella Vista']
            ],
            'Carabobo' => [
                'Valencia' => ['Valencia', 'Naguanagua']
            ]
        ];

        foreach ($estados as $estadoNombre => $municipios) {
            $estado = State::firstOrCreate([
                'country_id' => $venezuela->id,
                'name' => $estadoNombre
            ]);

            foreach ($municipios as $municipioNombre => $parroquias) {
                $municipio = Municipality::firstOrCreate([
                    'state_id' => $estado->id,
                    'name' => $municipioNombre
                ]);

                // Si tiene parroquias definidas
                if (!empty($parroquias)) {
                    foreach ($parroquias as $parroquiaNombre => $ciudades) {
                        $parroquia = Parish::firstOrCreate([
                            'municipality_id' => $municipio->id,
                            'name' => $parroquiaNombre
                        ]);

                        // Crear ciudades para esta parroquia
                        if (!empty($ciudades) && is_array($ciudades)) {
                            foreach ($ciudades as $ciudadNombre) {
                                City::firstOrCreate([
                                    'parish_id' => $parroquia->id,
                                    'name' => $ciudadNombre
                                ]);
                            }
                        }
                    }
                } else {
                    // Si no tiene parroquias definidas, crear una genérica
                    $parroquia = Parish::firstOrCreate([
                        'municipality_id' => $municipio->id,
                        'name' => $municipioNombre
                    ]);
                }
            }
        }

        $this->command->info('✅ Ubicaciones de Venezuela sembradas correctamente.');
    }
}
