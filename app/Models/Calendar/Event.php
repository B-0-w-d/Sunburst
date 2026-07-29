<?php

namespace App\Models\Calendar;

use MongoDB\Laravel\Eloquent\Model;

class Event extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'events';

    protected $fillable = [
        'organizer_id',
        'title',
        'type',              // PRACTICE, MEETING, SHOW, EVENT
        'status',            // POLL, CONFIRMED, CANCELLED
        'target_member_ids', // danh sách ObjectId thành viên tham gia
        'poll_config',       // json cấu hình thời gian khảo sát
        'start_time',        // timestamp khi chốt lịch
        'end_time',          // timestamp khi chốt lịch
    ];

    protected $casts = [
        'target_member_ids' => 'array',
        'poll_config' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(Member::class, 'organizer_id', '_id');
    }

    public function availabilities()
    {
        return $this->hasMany(MemberAvailability::class, 'event_id', '_id');
    }
}
