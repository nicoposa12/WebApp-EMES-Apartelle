<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewReceived extends Notification
{
    use Queueable;

    protected $review;

    /**
     * Create a new notification instance.
     */
    public function __construct($review)
    {
        $this->review = $review;
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
        $guestName = $this->review->user->name ?? 'Guest';
        $roomNumber = $this->review->room->room_number ?? '';
        return [
            'type' => 'new_review',
            'icon' => 'bi-chat-left-heart-fill',
            'title' => 'New Guest Review!',
            'message' => "{$guestName} gave a {$this->review->rating}-star review for Room #{$roomNumber}.",
            'action_url' => "/rooms/" . $this->review->room_id,
            'action_text' => 'View Review',
            'review_id' => $this->review->id
        ];
    }
}
