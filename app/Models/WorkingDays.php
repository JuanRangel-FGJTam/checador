<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingDays extends Model
{
    protected $fillable = [
        'employee_id',
        'week',
        'weekend',
        'holidays'
    ];

    use HasFactory;
}
