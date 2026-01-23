<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessGranted extends Notification implements ShouldQueue
{
    use Queueable;

    public string $roleName;
    public string $churchName;

    public function __construct(string $roleName, string $churchName)
    {
        $this->roleName = $roleName;
        $this->churchName = $churchName;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Вам надано доступ — Ministrify')
            ->greeting('Вітаємо!')
            ->line("Вам надано доступ до системи церкви «{$this->churchName}».")
            ->line("Ваша роль: **{$this->roleName}**")
            ->action('Увійти в систему', url('/dashboard'))
            ->line('Тепер ви можете користуватися всіма функціями відповідно до вашої ролі.')
            ->salutation('З повагою, команда Ministrify');
    }
}
