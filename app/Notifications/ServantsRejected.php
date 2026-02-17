<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServantsRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $roleName,
        private ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("❌ Ваша заявка на роль {$this->roleName} відхилена")
            ->greeting("Привіт {$notifiable->name}!")
            ->line("На жаль, ваша заявка на роль **{$this->roleName}** була відхилена адміністратором церкви.");

        if ($this->reason) {
            $mail->line("**Причина:** {$this->reason}");
        }

        $mail->line("Ви можете спробувати подати нову заявку пізніше.")
            ->action('Перейти до панелі', route('dashboard'))
            ->line('Дякуємо розуміння!');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'role' => $this->roleName,
            'reason' => $this->reason,
        ];
    }
}
