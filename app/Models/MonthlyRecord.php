<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\MorphOne;

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
        'data',
        'filePath'
    ];

    public function process(){
        return $this->morphOne(Process::class, 'processable');
    }

}
