<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\Calendar\Event;

class Setlist extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'setlist';
    protected $fillable = ['show_id', 'song_id', 'event_id', 'description', 'target_member_ids'];

    protected $casts = [
        'target_member_ids' => 'array',
    ];

    public function show()
    {
        return $this->belongsTo(Show::class, 'show_id');
    }

    public function song()
    {
        return $this->belongsTo(Song::class, 'song_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }
}
