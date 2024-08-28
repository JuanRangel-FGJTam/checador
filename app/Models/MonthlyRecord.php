<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MonthlyRecord extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'general_direction_id',
        'direction_id',
        'subdirectorate_id',
        'department_id',
        'year',
        'month',
        'all_employees',
        'data'
    ];

}
