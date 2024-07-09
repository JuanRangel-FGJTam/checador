<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelRh extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvRh';
    protected $table = 'NIVEL';
    protected $primaryKey = 'IDNIVEL';
}
