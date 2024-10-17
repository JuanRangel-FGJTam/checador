<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelRh extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvRH';
    protected $table = 'NIVEL';
    protected $primaryKey = 'IDNIVEL';
}
