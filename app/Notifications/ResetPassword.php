<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ResetPassword extends Notification
{
    use Queueable;

    public string $token;
    public bool $isInvite;

    public function __construct(string $token, bool $isInvite = false)
    {
        $this->token = $token;
        $this->isInvite = $isInvite;
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

        // Check if user has ever logged in (has sessions)
        $hasLoggedIn = DB::table('sessions')
            ->where('user_id', $notifiable->id)
            ->exists();

        // If explicitly marked as invite OR user never logged in - it's an invitation
        $isInvitation = $this->isInvite || !$hasLoggedIn;

        if ($isInvitation) {
            return (new MailMessage)
                ->subject('Запрошення до Ministrify')
                ->greeting('Вітаємо!')
                ->line('Вас запросили до системи управління церквою Ministrify.')
                ->line('Натисніть кнопку нижче, щоб встановити пароль для вашого акаунту:')
                ->action('Встановити пароль', $url)
                ->line('Це посилання дійсне протягом 60 хвилин.')
                ->line('Якщо ви не очікували цього листа, просто проігноруйте його.')
                ->salutation('З повагою, команда Ministrify');
        }

        // Password reset for existing user
        return (new MailMessage)
            ->subject('Скидання пароля — Ministrify')
            ->greeting('Вітаємо!')
            ->line('Ви отримали цей лист, тому що було запитано скидання пароля для вашого акаунту.')
            ->line('Натисніть кнопку нижче, щоб встановити новий пароль:')
            ->action('Скинути пароль', $url)
            ->line('Це посилання дійсне протягом 60 хвилин.')
            ->line('Якщо ви не запитували скидання пароля, жодних дій не потрібно.')
            ->salutation('З повагою, команда Ministrify');
    }
}
