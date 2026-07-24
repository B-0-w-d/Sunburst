<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

// Model đại diện cho bảng/collection mã kích hoạt tài khoản đăng ký
class ActivationKey extends Model
{
    // Chỉ định kết nối cơ sở dữ liệu sử dụng là MongoDB
    protected $connection = 'mongodb';

    // Tên collection trong database
    protected $collection = 'activation_keys';

    // Các trường dữ liệu cho phép gán giá trị hàng loạt (mass assignment)
    protected $fillable = ['key_value', 'starts_at', 'expires_at'];

    // Ép kiểu dữ liệu các trường thời gian sang dạng đối tượng Carbon/datetime
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
