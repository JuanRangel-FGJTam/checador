<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Direction;

class DirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Direction::insert([
            ['id' => 1, 'name' => 'DESCONOCIDO', 'general_direction_id' => 1 ],
            ['id' => 2, 'name' => 'DGTIT', 'general_direction_id' => 2 ],
            ['id' => 3, 'name' => 'Fiscalía Especializada en la Investigación de Violaciones a Derechos Humanos', 'general_direction_id' => 6,],
            ['id' => 4, 'name' => 'Unidad de Servicios Forenses y Multidisciplinarios', 'general_direction_id' => 20, ],
            ['id' => 5, 'name' => 'Unidad de Operaciones Estratégicas y Reacción Inmediata', 'general_direction_id' => 20,],
            ['id' => 6, 'name' => 'Unidad Matamoros', 'general_direction_id' => 20 ],
            ['id' => 7, 'name' => 'Unidad Tampico', 'general_direction_id' => 20 ],
            ['id' => 8, 'name' => 'Fiscalía Especializada en la Investigación de Violaciones a Derechos Humanos', 'general_direction_id' => 23],
            ['id' => 11, 'name' => 'Subdirección de Administración de Tecnologías de la Información y Comunicaciones', 'general_direction_id' => 2],
            ['id' => 12, 'name' => 'Subdirección de Soluciones Ejecutivas, Análisis de Información y Estadística', 'general_direction_id' => 2],
            ['id' => 13, 'name' => 'Subdirección de Soluciones Tecnológicas', 'general_direction_id' => 2],
            ['id' => 14, 'name' => 'UECS Altamira', 'general_direction_id' => 15],
            ['id' => 15, 'name' => 'UECS Reynosa', 'general_direction_id' => 15],
            ['id' => 16, 'name' => 'UECS Matamoros', 'general_direction_id' => 15],
            ['id' => 17, 'name' => 'Unidad de Investigación y Litigación', 'general_direction_id' => 16]
        ]);
    }
}
