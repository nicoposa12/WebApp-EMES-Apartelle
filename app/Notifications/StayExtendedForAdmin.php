<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StayExtendedForAdmin extends Notification
{
    use Queueable;

    protected $reservation;
    protected $oldCheckout;

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation, $oldCheckout)
    {
        $this->reservation = $reservation;
        $this->oldCheckout = $oldCheckout;
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
        $guestName = $this->reservation->user->name ?? 'Guest';
        $roomNumber = $this->reservation->room->room_number ?? '';
        return [
            'type' => 'stay_extended',
            'icon' => 'bi-calendar-plus-fill',
            'title' => 'Stay Extended!',
            'message' => "Guest {$guestName} extended stay in Room #{$roomNumber} until " . date('M d, Y', strtotime($this->reservation->check_out)) . ".",
            'action_url' => '/admin/reservations',
            'action_text' => 'View Reservations',
            'reservation_id' => $this->reservation->id
        ];
    }
}
