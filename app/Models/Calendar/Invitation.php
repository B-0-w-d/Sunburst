<?php

namespace App\Models\Calendar;

use MongoDB\Laravel\Eloquent\Model;

class Invitation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'invitations';

    protected $fillable = [
        'event_id',
        'member_id',
        'rsvp_status',       // YES, NO, MAYBE, PENDING
        'response_timestamp',
    ];

    protected $casts = [
        'response_timestamp' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', '_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', '_id');
    }
}
