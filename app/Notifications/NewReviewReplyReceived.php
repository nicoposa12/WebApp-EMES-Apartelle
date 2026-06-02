<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewReplyReceived extends Notification
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
        $roomNumber = $this->review->room->room_number ?? '';
        return [
            'type' => 'new_reply',
            'icon' => 'bi-chat-right-text-fill',
            'title' => 'Response from EME\'s Apartelle',
            'message' => "Management replied to your review on Room #{$roomNumber}.",
            'action_url' => "/rooms/" . $this->review->room_id,
            'action_text' => 'Read Response',
            'review_id' => $this->review->id
        ];
    }
}
