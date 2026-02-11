<?php

return [
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'alert_bot_token' => env('ALERT_TELEGRAM_BOT_TOKEN'),
        'alert_chat_id' => env('ALERT_TELEGRAM_CHAT_ID'),
    ],

    'uptime' => [
        'webhook_secret' => env('UPTIME_WEBHOOK_SECRET'),
    ],

    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@ministrify.app'),
    ],

    'liqpay' => [
        'public_key' => env('LIQPAY_PUBLIC_KEY'),
        'private_key' => env('LIQPAY_PRIVATE_KEY'),
        'sandbox' => env('LIQPAY_SANDBOX', false),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/google/callback',  // For Socialite (Google Login)
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/settings/google-calendar/callback'),  // For Google Calendar
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5-20250929'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
        'admin_chat_id' => env('CLAUDE_ADMIN_CHAT_ID'),
    ],
];
