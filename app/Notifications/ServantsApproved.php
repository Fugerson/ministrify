<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServantsApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $roleName,
        private string $churchName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("✅ Вас одобрено як служителя в {$this->churchName}")
            ->greeting("Привіт {$notifiable->name}!")
            ->line("Ваша заявка на роль **{$this->roleName}** в церкві **{$this->churchName}** була одобрена!")
            ->line("Тепер у вас є доступ до всіх функцій служителя.")
            ->action('Перейти до панелі', route('dashboard'))
            ->line('Дякуємо за участь у служінні!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'role' => $this->roleName,
            'church' => $this->churchName,
        ];
    }
}
