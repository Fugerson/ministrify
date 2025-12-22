<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$church = App\Models\Church::first();

echo "Church: " . $church->name . "\n";
echo "Telegram token: " . ($church->telegram_bot_token ? 'SET (' . strlen($church->telegram_bot_token) . ' chars)' : 'NOT SET') . "\n";
echo "Settings: " . json_encode($church->settings ?? [], JSON_PRETTY_PRINT) . "\n";
