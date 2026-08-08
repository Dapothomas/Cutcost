<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShopAlert extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $href = null,
        public string $type = 'info',
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'href' => $this->href,
            'type' => $this->type,
        ];
    }
}
