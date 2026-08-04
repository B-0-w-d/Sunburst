<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model; // Dùng Model chuẩn của MongoDB

class Song extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'songs';

    protected $fillable = [
        'title',
        'default_description',
        'notes',
    ];
}
