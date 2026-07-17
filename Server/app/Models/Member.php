<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    use HasApiTokens;

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

            if (empty($member->status)) {
                $member->status = 'active';
            }

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

            // 3. FIXED: Safe password mutation logic to avoid accidental unsets in MongoDB
            if ($member->isDirty('password')) {
                if (!empty($member->password)) {
                    $member->password = Hash::make($member->password);
                } else {
                    // Fallback to the original value if it was passed empty in an update payload
                    $member->password = $member->getOriginal('password');
                }
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

    public function isManagementTier(): bool
    {
        $highRoles = ['admin', 'president', 'vice-president', 'manager'];
        return in_array(strtolower($this->role ?? ''), $highRoles);
    }
}
