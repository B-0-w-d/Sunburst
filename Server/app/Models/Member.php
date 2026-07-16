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
            // Fallback default setups
            if (empty($member->role)) {
                $member->role = 'member';
            }

            // Auto-assign operational status for UI filtering if not provided
            if (empty($member->status)) {
                $member->status = 'active';
            }

            // Set the joining timestamp automatically
            if (empty($member->joined_in)) {
                $member->joined_in = now()->toDateTimeString();
            }

            if (isset($member->instrument) && !is_array($member->instrument)) {
                $member->instrument = array_filter(explode(',', $member->instrument));
            }

            $member->password = Hash::make($member->password ?? '12345678');
        });

        static::updating(function (Member $member) {
            if ($member->isDirty('instrument') && !is_array($member->instrument)) {
                $member->instrument = array_filter(explode(',', $member->instrument));
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
