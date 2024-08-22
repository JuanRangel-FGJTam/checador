<?php

namespace Database\Seeders;

use App\Models\IncidentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncidentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IncidentType::insert([
            ["id" => 1, "name" => "Falta"],
            ["id" => 2, "name" => "Retardo"],
            ["id" => 3, "name" => "Retardo Mayor"],
            ["id" => 4, "name" => "Omisión de Entrada"],
            ["id" => 5, "name" => "Omisión de Salida"],
            ["id" => 6, "name" => "Retardo Entrada (Comida)"],
            ["id" => 7, "name" => "Omisión de Entrada (Comida)"],
            ["id" => 8, "name" => "Omisión de Salida (Comida)"],
            ["id" => 9, "name" => "Salió antes de su horario (Comida)"],
            ["id" => 10, "name" => "Falta (Comida)"]
        ]);
    }
}
