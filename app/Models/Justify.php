<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justify extends Model
{
    use HasFactory;

    public function type() 
    {
        return $this->belongsTo(TypeJustify::class, 'type_justify_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
