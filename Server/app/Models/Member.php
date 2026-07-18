<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    // Sử dụng trait để hỗ trợ API Tokens (Sanctum)
    use HasApiTokens;

    // Cấu hình kết nối MongoDB và tên collection
    protected $connection = 'mongodb';
    protected $collection = 'members';

    // Cấu hình Primary Key là _id của MongoDB (string)
    protected $primaryKey = '_id';
    protected $keyType = 'string';

    // Các trường dữ liệu cho phép gán (Mass Assignment)
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'instrument',
        'birthday',
        'joined_in',
        'status',
        'background_image',
        'phone_number'
    ];

    /**
     * Ghi đè phương thức xác thực của Laravel
     * Để Laravel hiểu trường định danh là '_id' thay vì 'id' mặc định
     */
    public function getAuthIdentifierName()
    {
        return '_id';
    }

    public function getAuthIdentifier()
    {
        return (string) $this->_id;
    }

    /**
     * Model Events: Tự động xử lý dữ liệu trước khi lưu vào database
     */
    protected static function booted()
    {
        // Sự kiện khi tạo mới một Member
        static::creating(function (Member $member) {
            // Thiết lập giá trị mặc định nếu không có
            $member->role = $member->role ?? 'member';
            $member->status = $member->status ?? 'active';
            $member->joined_in = $member->joined_in ?? now()->toDateTimeString();

            // Nếu instrument là chuỗi trống hoặc null, gán là mảng rỗng để MongoDB lưu đúng kiểu
            if (empty($member->instrument)) {
                $member->instrument = [];
            } elseif (!is_array($member->instrument)) {
                // Chuyển đổi chuỗi "guitar,drum" thành mảng
                $member->instrument = array_filter(array_map('trim', explode(',', $member->instrument)));
            }

            // Hash mật khẩu (mặc định là 12345678 nếu không truyền vào)
            $member->password = Hash::make($member->password ?? '12345678');
        });

        // Sự kiện khi cập nhật thông tin Member
        static::updating(function (Member $member) {
            // Kiểm tra và chuyển đổi định dạng mảng cho 'instrument' nếu bị thay đổi
            if ($member->isDirty('instrument') && !is_array($member->instrument)) {
                $member->instrument = array_filter(explode(',', $member->instrument));
            }

            // Hash mật khẩu mới nếu có thay đổi
            if ($member->isDirty('password') && !empty($member->password)) {
                $member->password = Hash::make($member->password);
            }
        });
    }

    /**
     * Helper: Kiểm tra quyền quản trị
     * Dùng để phân quyền trong các Controller hoặc Policy
     */
    public function isManagementTier(): bool
    {
        return in_array(strtolower($this->role ?? ''), ['admin', 'president', 'vice-president', 'manager']);
    }
    //* Scope áp dụng bộ lọc cho truy vấn Member.
    public function scopeWithFilters($query, array $filters)
    {
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['instrument'])) {
            $query->where('instrument', $filters['instrument']);
        }


        return $query;
    }
}
