<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ActivateAccount extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = (int) config('auth.activation_expire', 60);
        $url = URL::temporarySignedRoute('activation.verify', now()->addMinutes($minutes), [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->email),
        ]);

        return (new MailMessage)
            ->subject('Activate your '.config('app.name').' account')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('One more step: confirm your email address to activate your account.')
            ->action('Activate my account', $url)
            ->line("This link expires in {$minutes} minutes. You can request another from the login page.")
            ->line('If you did not create this account, you can ignore this email.');
    }
}
