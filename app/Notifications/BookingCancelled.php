<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
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
        return [
            'type' => 'alert',
            'icon' => 'bi bi-calendar-x-fill',
            'title' => 'Booking Cancelled',
            'message' => 'The reservation for ' . ($this->reservation->room->room_type ?? 'Room') . ' #' . ($this->reservation->room->room_number ?? '') . ' has been cancelled.',
            'action_url' => '/my-bookings',
            'action_text' => 'View History'
        ];
    }
}
