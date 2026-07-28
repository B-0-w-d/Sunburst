<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Calendar extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'calendars';

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'type',         // show, rehearsal, birthday, event...
        'reference_id', // Liên kết ngược về collection gốc nếu cần
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
}
