<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GeneralDirection;

class GeneralDirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GeneralDirection::create([
            'id' => 1, 
            'name' => 'DESCONOCIDO', 
            'abbreviation' => 'DESC'
        ]);
        GeneralDirection::create([
            'id' => 2, 
            'name' => 'Dirección General de Tecnología, Información y Telecomunicaciones', 
            'abbreviation' => 'DGTIT'
        ]);
        GeneralDirection::create([
            'id' => 3, 
            'name' => 'Dirección General de Administración', 
            'abbreviation' => 'DGA'
        ]);
        GeneralDirection::create([
            'id' => 4, 
            'name' => 'Dirección General de Formación y Servicio Profesional de Carrera', 
            'abbreviation' => 'DGFSPC'
        ]);
        GeneralDirection::create([
            'id' => 5, 
            'name' => 'Fiscalia Especializada en Delitos Electorales', 
            'abbreviation' => 'FEDE'
        ]);
        GeneralDirection::create([
            'id' => 6, 
            'name' => 'Dirección General de Asuntos Jurídicos y de Derechos Humanos', 
            'abbreviation' => 'DGAJDH'
        ]);
        GeneralDirection::create([
            'id' => 7, 
            'name' => 'Comisaria General de Investigación', 
            'abbreviation' => 'CGI'
        ]);
        GeneralDirection::create([
            'id' => 8, 
            'name' => 'Dirección General de Servicios Periciales y Ciencias Forenses', 
            'abbreviation' => 'DGSPCF'
        ]);
        GeneralDirection::create([
            'id' => 11, 
            'name' => 'Secretaría Particular', 
            'abbreviation' => 'SP'
        ]);
        GeneralDirection::create([
            'id' => 12, 
            'name' => 'Coordinación de la Oficina del Fiscal General', 
            'abbreviation' => 'COFG'
        ]);
        GeneralDirection::create([
            'id' => 13, 
            'name' => 'Dirección de Comunicación Social', 
            'abbreviation' => 'DCS'
        ]);
        GeneralDirection::create([
            'id' => 14, 
            'name' => 'Dirección de Vinculación y Enlace', 
            'abbreviation' => 'DVE'
        ]);
        GeneralDirection::create([
            'id' => 15, 
            'name' => 'Unidad Especializada en Combate al Secuestro', 
            'abbreviation' => 'UECS'
        ]);
        GeneralDirection::create([
            'id' => 16, 
            'name' => 'Vicefiscalía de Litigación y Control de Procesos y Constitucionalidad', 
            'abbreviation' => 'VLCPC'
        ]);
        GeneralDirection::create([
            'id' => 17, 
            'name' => 'Dirección de Control de Procesos', 
            'abbreviation' => 'DCP'
        ]);
        GeneralDirection::create([
            'id' => 18, 
            'name' => 'Dirección General de Averiguaciones Previas y Control de Procesos', 
            'abbreviation' => 'DGAP'
        ]);
        GeneralDirection::create([
            'id' => 19, 
            'name' => 'Unidad Técnica', 
            'abbreviation' => 'UT'
        ]);
        GeneralDirection::create([
            'id' => 20, 
            'name' => 'Fiscalía Especializada en la Investigación de los Delitos de Desaparición Forzada de Personas', 
            'abbreviation' => 'FEIDDFP'
        ]);
        GeneralDirection::create([
            'id' => 23, 
            'name' => 'Vicefiscalía de Delitos de Alto Impacto y de Violaciones a Derechos Humanos', 
            'abbreviation' => 'VDAIVDH'
        ]);

    }
}
