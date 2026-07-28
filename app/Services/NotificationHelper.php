<?php

use App\Models\SystemNotification;

// Kiểm tra xem hàm send_system_notification đã tồn tại chưa để tránh xung đột
if (!function_exists('send_system_notification')) {
    /**
     * Hàm helper gửi thông báo hệ thống.
     *
     * @param array $data Mảng dữ liệu đầu vào chứa thông tin thông báo
     * @return \App\Models\SystemNotification Bản ghi thông báo vừa được tạo
     */
    function send_system_notification(array $data)
    {
        // Tạo mới một bản ghi thông báo trong cơ sở dữ liệu
        return SystemNotification::create([
            'type'         => $data['type'] ?? 'personal',
            'recipient_id' => $data['recipient_id'] ?? null,
            'sender_id'    => $data['sender_id'] ?? null,
            'title'        => $data['title'],
            'message'      => $data['message'],
            'read_at'      => null,
        ]);
    }
}
