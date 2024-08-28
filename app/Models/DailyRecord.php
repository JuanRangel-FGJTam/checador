<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DailyRecord extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'general_direction_id',
        'direction_id',
        'subdirectorate_id',
        'department_id',
        'report_date',
        'all_employees',
        'data'
    ];

}
