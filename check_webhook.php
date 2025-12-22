<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$church = DB::table('churches')->first();
$token = $church->telegram_bot_token ?? null;

echo "Church: " . $church->name . "\n";
echo "Token from DB: " . ($token ? substr($token, 0, 15) . '...' : 'NULL') . "\n\n";

if (!$token) {
    echo "Token not set!\n";
    exit;
}

$response = file_get_contents("https://api.telegram.org/bot{$token}/getWebhookInfo");
$data = json_decode($response, true);

echo "Webhook URL: " . ($data['result']['url'] ?: 'NOT SET') . "\n";
echo "Pending updates: " . ($data['result']['pending_update_count'] ?? 0) . "\n";
echo "Last error: " . ($data['result']['last_error_message'] ?? 'none') . "\n";

echo "\nApp URL: " . config('app.url') . "\n";
echo "Expected webhook: " . url('/api/telegram/webhook') . "\n";
