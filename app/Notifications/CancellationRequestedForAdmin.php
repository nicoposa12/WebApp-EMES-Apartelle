<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CancellationRequestedForAdmin extends Notification
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
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
        $roomNum = $this->reservation->room->room_number ?? '';
        $guestName = $this->reservation->user->name ?? 'Guest';

        return [
            'type' => 'cancellation_request',
            'icon' => 'bi-x-circle-fill',
            'title' => 'Cancellation Requested!',
            'message' => 'Guest ' . $guestName . ' has requested cancellation for Room #' . $roomNum . '.',
            'action_url' => '/admin/reservations?id=' . $this->reservation->id,
            'action_text' => 'Review Request',
            'reservation_id' => $this->reservation->id
        ];
    }
}
