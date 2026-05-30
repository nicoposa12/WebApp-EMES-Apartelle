<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBookingForAdmin extends Notification
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
            'type' => 'reservation', // Custom type to distinguish for SweetAlert
            'icon' => 'bi-calendar-check-fill',
            'title' => 'New Reservation!',
            'message' => 'Guest ' . ($this->reservation->user->name ?? 'User') . ' booked Room #' . $this->reservation->room->room_number . ' for ' . $this->reservation->check_in . '.',
            'action_url' => '/admin/reservations',
            'action_text' => 'View Details',
            'reservation_id' => $this->reservation->id
        ];
    }
}
