<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Direction;

class Subdirectorate extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "name",
        "direction_id"
    ];

    /**
     * Get the user that owns the Subdirectorate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

}
