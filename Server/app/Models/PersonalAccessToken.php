<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ActivityLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'activity_logs';

    protected $fillable = [
        'member_id',
        'token_id',
        'action',
        'details',
        'created_at'
    ];

    // Quan hệ với Member
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', '_id');
    }
}
