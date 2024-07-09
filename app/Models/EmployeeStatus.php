<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeStatus extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvRh';
    protected $table = 'ESTADOEMPLEADO';
    protected $primaryKey = 'IDESTADOEMPLEADO';
}
