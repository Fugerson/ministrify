<?php

use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

// Telegram webhook with rate limiting and validation
Route::middleware(['throttle:120,1', 'telegram.webhook'])->group(function () {
    Route::post('telegram/webhook', [TelegramController::class, 'webhook']);
});
Route::get('telegram/link/{code}', [TelegramController::class, 'link'])->name('telegram.link')->middleware('throttle:10,1');

// Payment webhooks (for donations)
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

// PWA Offline API (requires auth via web session)
Route::prefix('pwa')->name('api.pwa.')->middleware(['web', 'auth'])->group(function () {
    Route::get('my-schedule', [\App\Http\Controllers\Api\MyScheduleController::class, 'index'])->name('my-schedule');
    Route::post('responsibilities/{id}/confirm', [\App\Http\Controllers\Api\MyScheduleController::class, 'confirm'])->name('responsibilities.confirm');
    Route::post('responsibilities/{id}/decline', [\App\Http\Controllers\Api\MyScheduleController::class, 'decline'])->name('responsibilities.decline');
});

// QR Check-in API
Route::prefix('checkin')->name('api.checkin.')->middleware(['web'])->group(function () {
    Route::post('{token}', [\App\Http\Controllers\QrCheckinController::class, 'checkin'])->name('checkin')->middleware('auth');
    Route::get('today-events', [\App\Http\Controllers\QrCheckinController::class, 'todayEvents'])->name('today-events')->middleware('auth');
    Route::post('admin', [\App\Http\Controllers\QrCheckinController::class, 'adminCheckin'])->name('admin')->middleware('auth');
});
