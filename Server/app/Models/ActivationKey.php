<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ActivationKey extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'activation_keys';
    protected $fillable = ['key_value', 'starts_at', 'expires_at'];
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
