<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Herstel uw wachtwoord voor Ontwikkeling')
            ->greeting('Hallo '.$notifiable->naam.',')
            ->line('Er is een verzoek ontvangen om het wachtwoord van uw account te herstellen.')
            ->action('Wachtwoord herstellen', $url)
            ->line('Deze herstellink is 60 minuten geldig en kan maar één keer worden gebruikt.')
            ->line('Heeft u dit niet aangevraagd? Dan hoeft u niets te doen; uw huidige wachtwoord blijft geldig.');
    }
}
