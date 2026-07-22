<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendMemberNotification extends Notification
{
    use Queueable;

    protected $data;

    // Nhận dữ liệu truyền vào khi gọi thông báo
    public function __construct($data)
    {
        $this->data = $data;
    }

    // Xác định kênh gửi thông báo (ở đây chọn database)
    public function via($notifiable)
    {
        return ['database']; // Có thể thêm 'mail', 'broadcast', ...
    }

    // Dữ liệu sẽ được lưu vào cơ sở dữ liệu (dưới dạng mảng/JSON)
    public function toArray($notifiable)
    {
        return [
            'title' => $this->data['title'] ?? 'Thông báo mới',
            'message' => $this->data['message'] ?? '',
            'url' => $this->data['url'] ?? null,
        ];
    }
}
