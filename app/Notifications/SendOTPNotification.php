<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOTPNotification extends Notification
{
    use Queueable;

    protected $otpCode;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otpCode)
    {
        $this->otpCode = $otpCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Two-Factor Authentication OTP Code')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('You are receiving this email because a login request was initiated for your account at EME\'s Apartelle.')
            ->line('Use the following 6-digit One-Time Password (OTP) to complete your login. This code is valid for the next 60 seconds.')
            ->line('')
            ->line('**' . $this->otpCode . '**')
            ->line('')
            ->line('If you did not attempt to log in to your account, please ignore this email and ensure your password is secure.')
            ->salutation('Warm regards, EME\'s Apartelle Management Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
