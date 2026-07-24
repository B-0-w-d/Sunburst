<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

// Model đại diện cho collection lưu trữ thông báo hệ thống và thông báo cá nhân trong MongoDB
class SystemNotification extends Model
{
    // Cấu hình kết nối cơ sở dữ liệu MongoDB
    protected $connection = 'mongodb';
    protected $collection = 'system_notifications'; // Tên collection trong MongoDB

    // Các trường dữ liệu cho phép gán giá trị hàng loạt (Mass Assignment)
    protected $fillable = [
        'type',        // Phân loại: 'personal' (cá nhân) hoặc 'system' (hệ thống chung)
        'recipient_id',
        'sender_id',
        'title',
        'message',
        'read_at',
    ];
}
