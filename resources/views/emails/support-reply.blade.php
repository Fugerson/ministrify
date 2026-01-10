<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Відповідь на ваше звернення</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; border-collapse: collapse;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 30px; border-radius: 12px 12px 0 0; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                                Ministrify
                            </h1>
                            <p style="margin: 10px 0 0; color: rgba(255,255,255,0.8); font-size: 14px;">
                                Відповідь на ваше звернення
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <p style="margin: 0 0 20px; color: #374151; font-size: 16px;">
                                Вітаємо, {{ $ticket->guest_name ?? $ticket->user?->name ?? 'шановний користувач' }}!
                            </p>

                            <p style="margin: 0 0 20px; color: #6b7280; font-size: 14px;">
                                Ми відповіли на ваше звернення:
                            </p>

                            <!-- Ticket Info -->
                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                                <p style="margin: 0; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Тема звернення
                                </p>
                                <p style="margin: 5px 0 0; color: #111827; font-size: 16px; font-weight: 500;">
                                    {{ $ticket->subject }}
                                </p>
                            </div>

                            <!-- Reply -->
                            <div style="background-color: #eef2ff; border-left: 4px solid #6366f1; border-radius: 0 8px 8px 0; padding: 20px; margin-bottom: 20px;">
                                <p style="margin: 0 0 10px; color: #4f46e5; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Наша відповідь
                                </p>
                                <div style="margin: 0; color: #374151; font-size: 15px; line-height: 1.6; white-space: pre-wrap;">{{ $reply->message }}</div>
                            </div>

                            <p style="margin: 0 0 20px; color: #6b7280; font-size: 14px;">
                                Якщо у вас є додаткові питання, просто відповіть на цей лист або напишіть нам через
                                <a href="{{ url('/contact') }}" style="color: #6366f1; text-decoration: none;">контактну форму</a>.
                            </p>

                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

                            <p style="margin: 0; color: #9ca3af; font-size: 12px; text-align: center;">
                                З повагою,<br>
                                Команда Ministrify
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; text-align: center;">
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                &copy; {{ date('Y') }} Ministrify. Всі права захищено.
                            </p>
                            <p style="margin: 10px 0 0; color: #9ca3af; font-size: 11px;">
                                Цей лист було надіслано на адресу {{ $ticket->guest_email ?? $ticket->user?->email }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
