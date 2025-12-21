<?php

use App\Http\Controllers\Api\TelegramController;
use Illuminate\Support\Facades\Route;

// Telegram webhook
Route::post('telegram/webhook', [TelegramController::class, 'webhook']);
Route::get('telegram/link/{code}', [TelegramController::class, 'link'])->name('telegram.link');
