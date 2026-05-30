<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DisputeFiledForAdmin extends Notification
{
    use Queueable;

    protected $dispute;

    /**
     * Create a new notification instance.
     */
    public function __construct($dispute)
    {
        $this->dispute = $dispute;
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
        $roomNum = $this->dispute->reservation->room->room_number ?? '';
        $reasonLabel = ucwords(str_replace('_', ' ', $this->dispute->reason));

        return [
            'type' => 'dispute',
            'icon' => 'bi-exclamation-triangle-fill',
            'title' => 'New Dispute Filed!',
            'message' => 'Guest ' . ($this->dispute->user->name ?? 'Guest') . ' filed a dispute' . ($roomNum ? ' for Room #' . $roomNum : '') . ' regarding ' . $reasonLabel . '.',
            'action_url' => '/admin/disputes',
            'action_text' => 'Investigate Case',
            'dispute_id' => $this->dispute->id
        ];
    }
}
