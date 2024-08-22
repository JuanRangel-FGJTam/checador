<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeJustify;

class TypeJustifySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeJustify::insert([
            ["id" => 1, "name" => "Justificación por falla del Sistema"],
            ["id" => 2, "name" => "Suspensión por sanción disciplinaria"],
            ["id" => 3, "name" => "Permiso económico"],
            ["id" => 4, "name" => "Permiso por lactancia"],
            ["id" => 5, "name" => "Permiso de guardería"],
            ["id" => 6, "name" => "Permiso por cuidados maternos"],
            ["id" => 7, "name" => "Permiso por fallecimiento"],
            ["id" => 8, "name" => "Incapacidad del IMSS"],
            ["id" => 9, "name" => "Permiso sin goce de sueldo"],
            ["id" => 10, "name" => "Periodo vacacional"],
            ["id" => 11, "name" => "Vacaciones extemporaneas"],
            ["id" => 12, "name" => "Comisión oficial"],
            ["id" => 13, "name" => "Evento sindical"],
            ["id" => 14, "name" => "Motivos de salud (Médico)"],
            ["id" => 15, "name" => "Justificación de omisión de entrada"],
            ["id" => 16, "name" => "Justificación de omisión de salida"],
            ["id" => 17, "name" => "Justificación de día completo"],
            ["id" => 18, "name" => "Curso y/o Capacitación"],
            ["id" => 19, "name" => "Justificación de retardo menor"],
            ["id" => 20, "name" => "Justificación de retardo mayor"],
            ["id" => 21, "name" => "Incapacidad del ISSSTE"],
            ["id" => 22, "name" => "Incapacidad médico particular"],
            ["id" => 23, "name" => "Incapacidad Hospital General"],
            ["id" => 24, "name" => "Periodo de maternidad"],
            ["id" => 25, "name" => "Día de descanso obligatorio (Oficial)"],
            ["id" => 26, "name" => "Día de descanso no obligatorio (Autorizado)"],
            ["id" => 27, "name" => "Justificación de entrada por falla del Sistema"],
            ["id" => 28, "name" => "Justificación de salida por falla del Sistema"],
        ]);
        
    }
}
