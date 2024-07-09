<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::create(['id' => 1, 'name' => 'Admin', 'url' => 'admin', 'route' => 'admin']);
        Menu::create(['id' => 2, 'name' => 'Empleados', 'url' => 'rh', 'route' => 'rh']);
        Menu::create(['id' => 3, 'name' => 'Reportes', 'url' => 'reports', 'route' => 'reports']);
        Menu::create(['id' => 4, 'name' => 'Nuevos registros', 'url' => 'new-employees', 'route' => 'newEmployees']);
        Menu::create(['id' => 5, 'name' => 'Consulta', 'url' => 'show', 'route' => 'show']);
        Menu::create(['id' => 6, 'name' => 'Incidencias', 'url' => 'incidents', 'route' => 'incidents']);
        Menu::create(['id' => 7, 'name' => 'Días inhábiles', 'url' => 'hollidays', 'route' => 'hollidays']);
        Menu::create(['id' => 9, 'name' => 'Bajas', 'url' => 'inactive', 'route' => 'inactive']);
    }
}
