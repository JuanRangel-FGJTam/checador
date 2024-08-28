<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DailyRecord extends Model
{
    protected $connection = 'mongodb';
}
