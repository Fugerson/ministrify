<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PushSubscriptionController extends Controller
{
    /**
     * Get VAPID public key for client
     */
    public function getPublicKey(): JsonResponse
    {
        $publicKey = config('services.vapid.public_key');

        if (!$publicKey) {
            return response()->json([
                'error' => 'Push notifications not configured',
            ], 503);
        }

        return response()->json([
            'publicKey' => $publicKey,
        ]);
    }

    /**
     * Store a new push subscription
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|url|max:500',
            'keys.p256dh' => 'required|string|max:200',
            'keys.auth' => 'required|string|max:100',
        ]);

        $user = auth()->user();

        // Atomic upsert to prevent race conditions
        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'user_id' => $user->id,
                'p256dh_key' => $validated['keys']['p256dh'],
                'auth_key' => $validated['keys']['auth'],
                'user_agent' => $request->userAgent(),
                'is_active' => true,
            ]
        );

        $wasRecentlyCreated = $subscription->wasRecentlyCreated;

        return response()->json([
            'message' => $wasRecentlyCreated ? 'Підписку створено' : 'Підписку оновлено',
            'subscription_id' => $subscription->id,
        ], $wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Remove a push subscription
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $deleted = PushSubscription::where('endpoint', $validated['endpoint'])
            ->where('user_id', auth()->id())
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Підписку видалено',
            ]);
        }

        return response()->json([
            'message' => 'Підписку не знайдено',
        ], 404);
    }

    /**
     * Get user's subscription status
     */
    public function status(): JsonResponse
    {
        $user = auth()->user();
        $subscriptions = $user->pushSubscriptions()->active()->count();

        return response()->json([
            'subscribed' => $subscriptions > 0,
            'count' => $subscriptions,
        ]);
    }

    /**
     * Send test notification
     */
    public function test(WebPushService $pushService): JsonResponse
    {
        $user = auth()->user();
        $sent = $pushService->sendToUser($user, [
            'title' => 'Тестове сповіщення',
            'body' => 'Push-сповіщення працюють правильно!',
            'url' => '/dashboard',
            'icon' => '/icons/icon-192x192.png',
        ]);

        if ($sent > 0) {
            return response()->json([
                'message' => 'Тестове сповіщення надіслано',
                'sent_to' => $sent,
            ]);
        }

        return response()->json([
            'error' => 'Не вдалося надіслати сповіщення. Перевірте налаштування.',
        ], 500);
    }
}
