<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WorkingHours extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'checkin',
        'toeat',
        'toarrive',
        'checkout',
        'deleted_at'
    ];


    protected static function booted()
    {
        // * prevent having multiple workingHours active at the same time
        static::creating(function ($workingHour)
        {
            // ensure delete previous working hours
            WorkingHours::where('employee_id', $workingHour->employee_id)
                ->whereNull('deleted_at')
                ->delete();
        });
    }

}
