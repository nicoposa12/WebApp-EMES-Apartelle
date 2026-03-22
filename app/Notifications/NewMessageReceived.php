<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessageReceived extends Notification
{
    use Queueable;

    protected $senderName;
    protected $messageSnippet;

    /**
     * Create a new notification instance.
     */
    public function __construct($senderName, $messageSnippet)
    {
        $this->senderName = $senderName;
        $this->messageSnippet = $messageSnippet;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'info',
            'icon' => 'bi bi-chat-dots-fill',
            'title' => 'New Message from Support',
            'message' => $this->messageSnippet,
            'action_url' => '/contact', // Or wherever chat widget is usually interacted with
            'action_text' => 'Reply Now'
        ];
    }
}
