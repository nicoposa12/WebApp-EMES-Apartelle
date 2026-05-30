<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DisputeUpdatedForGuest extends Notification
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
        $statusLabel = ucwords(str_replace('_', ' ', $this->dispute->status));
        $roomNum = $this->dispute->reservation->room->room_number ?? '';

        return [
            'type' => 'dispute',
            'icon' => 'bi-info-circle-fill',
            'title' => 'Dispute Case Updated',
            'message' => 'Your dispute case' . ($roomNum ? ' for Room #' . $roomNum : '') . ' has been updated to: ' . $statusLabel . '.',
            'action_url' => '/my-bookings',
            'action_text' => 'View Dispute',
            'dispute_id' => $this->dispute->id
        ];
    }
}
