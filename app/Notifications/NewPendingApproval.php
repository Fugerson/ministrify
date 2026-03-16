<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPendingApproval extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $userName,
        private string $userEmail,
        private string $churchName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🔔 Новий користувач очікує одобрення — {$this->churchName}")
            ->greeting("Привіт {$notifiable->name}!")
            ->line("**{$this->userName}** ({$this->userEmail}) зареєструвався та очікує одобрення ролі в церкві **{$this->churchName}**.")
            ->action('Переглянути заявки', route('settings.servant-approvals.index'))
            ->line('Будь ласка, одобріть або відхиліть заявку.');
    }
}
