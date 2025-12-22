<?php

use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\PublicSiteController;
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

// Calendar API (public, token-based auth)
Route::prefix('calendar')->name('api.calendar.')->middleware('throttle:60,1')->group(function () {
    // iCal feed for subscriptions (Google Calendar, Apple Calendar, etc.)
    Route::get('feed', [CalendarController::class, 'feed'])->name('feed');

    // JSON API
    Route::get('events', [CalendarController::class, 'events'])->name('events');
    Route::get('events/{id}', [CalendarController::class, 'event'])->name('event');
    Route::get('ministries', [CalendarController::class, 'ministries'])->name('ministries');
});
