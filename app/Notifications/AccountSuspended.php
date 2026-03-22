<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountSuspended extends Notification
{
    use Queueable;

    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($reason)
    {
        $this->reason = $reason;
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
            'type' => 'alert',
            'icon' => 'bi bi-shield-exclamation',
            'title' => 'Account Suspended',
            'message' => 'Your account has been restricted: ' . ($this->reason ?? 'Violation of house rules.'),
            'action_url' => '/profile',
            'action_text' => 'View Status'
        ];
    }
}
