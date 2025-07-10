<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkingHours extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'checkin',
        'toeat',
        'toarrive',
        'checkout',
        'deleted_at',
        'updated_by'
    ];


    protected static function booted()
    {
        // * prevent having multiple workingHours active at the same time
        static::creating(function ($workingHour)
        {
            // * set the user id
            $workingHour->updated_by = auth()->user()?->id;

            // ensure delete previous working hours
            WorkingHours::where('employee_id', $workingHour->employee_id)
                ->whereNull('deleted_at')
                ->delete();
        });

        static::updating(function ($workingHour)
        {
            $workingHour->updated_by = auth()->user()?->id;
        });
    }

    /**
     * Get the user that owns the WorkingHours
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

}
