<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justify extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'employee_id',
        'type_justify_id',
        'date_start',
        'date_finish',
        'file',
        'details'
    ];

    protected $casts = [
        'date_start' => 'datetime:Y-m-d H:i:s',
        'date_finish' => 'datetime:Y-m-d H:i:s'
    ];

    public function type() 
    {
        return $this->belongsTo(TypeJustify::class, 'type_justify_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
