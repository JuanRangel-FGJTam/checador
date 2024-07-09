<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function state()
    {
        return $this->belongsTo(IncidentState::class, 'incident_state_id');
    }

    public function type()
    {
        return $this->belongsTo(IncidentType::class, 'incident_type_id');
    }
}
