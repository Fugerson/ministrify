<?php

use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

// Telegram webhook with rate limiting
Route::middleware('throttle:120,1')->group(function () {
    Route::post('telegram/webhook', [TelegramController::class, 'webhook']);
    Route::get('telegram/link/{code}', [TelegramController::class, 'link'])->name('telegram.link')->middleware('throttle:10,1');
});

// Payment webhooks
Route::prefix('webhooks')->name('api.webhooks.')->middleware('throttle:60,1')->group(function () {
    Route::post('liqpay', [PublicSiteController::class, 'liqpayCallback'])->name('liqpay');
});

// LiqPay subscription webhook
Route::post('liqpay/webhook', [\App\Http\Controllers\Api\LiqPayWebhookController::class, 'handle'])
    ->name('api.liqpay.webhook')
    ->middleware('throttle:60,1');

// Calendar API (public, token-based auth)
Route::prefix('calendar')->name('api.calendar.')->middleware('throttle:60,1')->group(function () {
    // iCal feed for subscriptions (Google Calendar, Apple Calendar, etc.)
    Route::get('feed', [CalendarController::class, 'feed'])->name('feed');

    // JSON API
    Route::get('events', [CalendarController::class, 'events'])->name('events');
    Route::get('events/{id}', [CalendarController::class, 'event'])->name('event');
    Route::get('ministries', [CalendarController::class, 'ministries'])->name('ministries');
});

// Push notifications API
Route::prefix('push')->name('api.push.')->group(function () {
    Route::get('public-key', [PushSubscriptionController::class, 'getPublicKey'])->name('public-key');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('subscribe', [PushSubscriptionController::class, 'store'])->name('subscribe');
        Route::delete('unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('unsubscribe');
        Route::get('status', [PushSubscriptionController::class, 'status'])->name('status');
        Route::post('test', [PushSubscriptionController::class, 'test'])->name('test');
    });
});
