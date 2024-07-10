<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Subdirectorate;

class Department extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'name', 
        'subdirectorate_id'
    ];

    /**
     * Get the user that owns the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subdirectorate(): BelongsTo
    {
        return $this->belongsTo(Subdirectorate::class);
    }

}
