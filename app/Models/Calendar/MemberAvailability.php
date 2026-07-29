<?php

namespace App\Models\Calendar;

use MongoDB\Laravel\Eloquent\Model;

class MemberAvailability extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'member_availabilities';

    protected $fillable = [
        'event_id',
        'member_id',
        'available_slots', // mảng chứa các mốc thời gian rảnh dạng ISO string
    ];

    protected $casts = [
        'available_slots' => 'array',
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
