<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GeneralDirection;

class Direction extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'general_direction_id'
    ];

    /**
     * Get the user that owns the Direction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function generalDirection(): BelongsTo
    {
        return $this->belongsTo(GeneralDirection::class);
    }

}
