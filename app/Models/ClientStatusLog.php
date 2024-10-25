<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientStatusLog extends Model
{

    protected $table = "clientsStatusLog";

    protected $fillable = [
        'id',
        'name',
        'address',
        'updated_at'
    ];

    use HasFactory;
}
