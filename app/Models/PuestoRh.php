<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuestoRh extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvRH';
    protected $table = 'PUESTO';
    protected $primaryKey = 'IDPUESTO';
}
