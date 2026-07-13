<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class Member extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'members';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'instrument',
        'birthday',
        'joined_in',
        'status'
    ];


    protected static function booted()
    {
        static::creating(function (Member $member) {
                if (empty($member->role)) {
                    $member->role = 'member';
                }
                if (isset($member->instrument)) {
                    $instruments = is_array($member->instrument)
                        ? $member->instrument
                        : array_filter(explode(',', $member->instrument));

                    sort($instruments); // <-- Alphabetize array items
                    $member->instrument = array_values($instruments); // Reset array keys cleanly
                }
                $member->password = Hash::make($member->password ?? '12345678');
            });

            static::updating(function (Member $member) {
                    if ($member->isDirty('instrument') && isset($member->instrument)) {
                        $instruments = is_array($member->instrument)
                            ? $member->instrument
                            : array_filter(explode(',', $member->instrument));

                        sort($instruments); // <-- Alphabetize array items
                        $member->instrument = array_values($instruments);
                    }
            if ($member->isDirty('password') && !empty($member->password)) {
                $member->password = Hash::make($member->password);
            } elseif (empty($member->password)) {
                unset($member->password);
            }
        });
    }

    public function scopeWithFilters(Builder $query, array $filters = [], array $sortParams = [])
    {
        if (!empty($filters)) {
            foreach (['role', 'status'] as $field) {
                if (!empty($filters[$field])) {
                    $query->where($field, $filters[$field]);
                }
            }

            if (!empty($filters['instrument'])) {
                $instrumentsArray = is_array($filters['instrument'])
                    ? $filters['instrument']
                    : array_filter(explode(',', $filters['instrument']));

                $query->whereIn('instrument', $instrumentsArray);
            }
        }

        if (!empty($sortParams['sortBy'])) {
            $direction = (strtolower($sortParams['sortOrder'] ?? '') === 'desc') ? 'desc' : 'asc';
            $query->orderBy($sortParams['sortBy'], $direction);
        }

        return $query;
    }
}
