<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SystemNotification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'system_notifications'; // Tên collection trong MongoDB

    protected $fillable = [
        'type',        // Phân loại: 'personal' (cá nhân) hoặc 'system' (hệ thống chung)
        'recipient_id', // ID của người nhận (null nếu là thông báo chung cho tất cả/admin)
        'sender_id',   // ID của người gây ra hành động (nếu có)
        'title',       // Tiêu đề thông báo
        'message',     // Nội dung chi tiết
        'read_at',     // Thời điểm đọc (null là chưa đọc)
    ];
}
