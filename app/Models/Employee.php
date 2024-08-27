<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'general_direction_id',
        'direction_id',
        'subdirectorate_id',
        'department_id',
        'name',
        'photo',
        'fingerprint',
        "plantilla_id",
        "active",
        "status_id"
    ];

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function direction() {
        return $this->belongsTo(Direction::class);
    }

    public function generalDirection() {
        return $this->belongsTo(GeneralDirection::class);
    }

    public function incidents() {
        return $this->hasMany(Incident::class);
    }

    public function records() {
        return $this->hasMany(Record::class);
    }

    public function subdirectorate() {
        return $this->belongsTo(Subdirectorate::class);
    }

    public function workingDays() {
        return $this->hasOne(WorkingDays::class);
    }

    public function workingHours() {
        return $this->hasOne(WorkingHours::class);
    }


    /**
     * return the employee number
     *
     * @return int
     */
    public function employeeNumber(){
        return intval( substr($this->plantilla_id, 1) );
    }

}
