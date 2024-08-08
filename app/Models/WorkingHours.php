<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingHours extends Model
{
    protected $fillable = [
        'employee_id',
        'checkin',
        'toeat',
        'toarrive',
        'checkout'
    ];
    
    use HasFactory;
}
