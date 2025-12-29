<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends Notification
{
    use Queueable;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Встановлення пароля — Ministrify')
            ->greeting('Вітаємо!')
            ->line('Ви отримали цей лист, тому що вас запросили до системи Ministrify.')
            ->line('Натисніть кнопку нижче, щоб встановити пароль для вашого акаунту:')
            ->action('Встановити пароль', $url)
            ->line('Це посилання дійсне протягом 60 хвилин.')
            ->line('Якщо ви не очікували цього листа, просто проігноруйте його.')
            ->salutation('З повагою, команда Ministrify');
    }
}
