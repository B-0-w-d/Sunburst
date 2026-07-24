<?php

use App\Models\SystemNotification;

if (!function_exists('send_system_notification')) {
    function send_system_notification(array $data)
    {
        return SystemNotification::create([
            'type'         => $data['type'] ?? 'personal', // 'personal' hoặc 'system'
            'recipient_id' => $data['recipient_id'] ?? null,
            'sender_id'    => $data['sender_id'] ?? null,
            'title'        => $data['title'],
            'message'      => $data['message'],
            'read_at'      => null,
        ]);
    }
}
