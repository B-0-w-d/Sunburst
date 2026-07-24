<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
use MongoDB\Laravel\Eloquent\DocumentModel;

// Model tùy chỉnh cho Laravel Sanctum PersonalAccessToken chạy trên MongoDB
class PersonalAccessToken extends SanctumToken
{
    // Sử dụng DocumentModel trait tương thích với MongoDB ODM
    use DocumentModel;

    // Cấu hình kết nối và tên bảng/collection
    protected $connection = 'mongodb';
    protected $table = 'personal_access_tokens';

    // Cấu hình khóa chính là _id kiểu chuỗi
    protected $primaryKey = '_id';
    protected $keyType = 'string';
}
