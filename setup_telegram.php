<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$token = '8479904016:AAFyygeJiK99gy5B9ilE3dGpbM3S75WLC24';

// Update church
$church = App\Models\Church::first();
$church->telegram_bot_token = $token;
$church->save();

echo "Token saved to church: {$church->name}\n";
echo "Token in DB: " . substr($church->telegram_bot_token, 0, 15) . "...\n\n";

// Test connection
$telegram = new App\Services\TelegramService($token);
try {
    $botInfo = $telegram->getMe();
    echo "Bot connected: @{$botInfo['username']}\n";
    echo "Bot name: {$botInfo['first_name']}\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Setup webhook
$webhookUrl = config('app.url') . '/api/telegram/webhook';
echo "Setting webhook to: {$webhookUrl}\n";

$result = $telegram->setWebhook($webhookUrl);
echo "Webhook setup: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";

// Check webhook
$response = file_get_contents("https://api.telegram.org/bot{$token}/getWebhookInfo");
$data = json_decode($response, true);
echo "Current webhook URL: " . ($data['result']['url'] ?: 'NOT SET') . "\n";
echo "Pending updates: " . ($data['result']['pending_update_count'] ?? 0) . "\n";
if (!empty($data['result']['last_error_message'])) {
    echo "Last error: " . $data['result']['last_error_message'] . "\n";
}
