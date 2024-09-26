<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRh extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvRH';
    protected $table = 'EMPLEADO';
    protected $primaryKey = 'IDEMPLEADO';

    public function plaza()
    {
        return $this->belongsTo(PlazaRh::class, 'IDPLAZA', 'IDPLAZA'); 
    }

    public function status()
    {
        return $this->belongsTo(EmployeeStatus::class, 'IDESTADOEMPLEADO', 'IDESTADOEMPLEADO');
    }
}
