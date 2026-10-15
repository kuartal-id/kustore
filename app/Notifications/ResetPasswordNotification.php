<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]);
        $minutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Reset your Kustore password')
            ->line('Someone asked to reset the password for your Kustore account.')
            ->action('Choose a new password', $url)
            ->line("This link expires in {$minutes} minutes.")
            ->line('If this wasn\'t you, you can ignore this email. Your password stays the same.');
    }
}
