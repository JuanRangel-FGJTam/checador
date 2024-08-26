<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeFile;

class TypesFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeFile::insert([
            ["id" => 1, "name" => "daily"],
            ["id" => 2, "name" => "monthly"],
            ["id" => 3, "name" => "yearly"],
            ["id" => 4, "name" => "Kardex"]
        ]);
        
    }
}
