<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\MorphTo;

class Process extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'status',
        'output',
        'started_at',
        'ended_at',
        'procesable_id',
        'procesable_type'
    ];

    /**
     * Get the parent imageable model (user or post).
     */
    public function processable(): MorphTo
    {
        return $this->morphTo();
    }

}
