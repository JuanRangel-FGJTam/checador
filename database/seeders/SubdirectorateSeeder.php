<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subdirectorate;

class SubdirectorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subdirectorate::insert([
            ['id' => 1, 'name' => 'DESCONOCIDO', 'direction_id' => 1,],
            ['id' => 2, 'name' => 'ESTADISTICA', 'direction_id' => 2,],
            ['id' => 3, 'name' => 'SISTEMAS', 'direction_id' => 2,],
            ['id' => 4, 'name' => 'SOPORTE', 'direction_id' => 2,],
            ['id' => 5, 'name' => 'Fiscalía Especializada en la Investigación de los Delitos de Tortura y Otros Tratos y Penas Crueles, Inhumanos y Degradantes', 'direction_id' => 3],
            ['id' => 6, 'name' => 'Unidad Especializada en la Investigación del Delito de Trata de Personas', 'direction_id' => 3],
            ['id' => 7, 'name' => 'Fiscalía Especializada en la Investigación del Delito de Tortura y Otros Tratos y Penas Crueles, Inhumanos o Degradantes.', 'direction_id' => 8],
            ['id' => 8, 'name' => 'Unidad Especializada en la Investigación del Delito de Trata de Personas', 'direction_id' => 8]
        ]);
    }
}
