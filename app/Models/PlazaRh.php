<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlazaRh extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvRH';
    protected $table = 'PLAZA';
    protected $primaryKey = 'IDPLAZA';

    public function nivel()
    {
        return $this->belongsTo(NivelRh::class, 'IDNIVEL', 'IDNIVEL');
    }

    public function puesto()
    {
        return $this->belongsTo(PuestoRh::class, 'IDPUESTO', 'IDPUESTO');
    }
}
