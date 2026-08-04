<?php

namespace App\Models;

// Thay vì use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model;

class Show extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'shows'; // Dùng collection thay cho table nếu dùng package mới
    protected $fillable = ['title', 'date', 'location', 'organizer_id'];

    public function setlist()
    {
        return $this->hasMany(Setlist::class, 'show_id');
    }

    public function organizer()
    {
        return $this->belongsTo(Member::class, 'organizer_id', 'id');
    }
}
