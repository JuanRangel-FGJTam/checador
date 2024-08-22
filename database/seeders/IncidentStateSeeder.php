<?php

namespace Database\Seeders;

use App\Models\IncidentState;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncidentStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IncidentState::insert([
            ["id" => 1, "name" => "Pendiente"],
            ["id" => 2, "name" => "Autorizado"],
            ["id" => 3, "name" => "Descontado"],
            ["id" => 4, "name" => "Cancelado"]
        ]);
    }
}
