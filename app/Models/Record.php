<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'check'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
