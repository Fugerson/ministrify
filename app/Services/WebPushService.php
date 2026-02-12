<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    private ?WebPush $webPush = null;

    /**
     * Get or create WebPush instance
     */
    private function getWebPush(): ?WebPush
    {
        if ($this->webPush) {
            return $this->webPush;
        }

        $vapidPublicKey = config('services.vapid.public_key');
        $vapidPrivateKey = config('services.vapid.private_key');
        $vapidSubject = config('services.vapid.subject');

        if (!$vapidPublicKey || !$vapidPrivateKey) {
            Log::warning('VAPID keys not configured');
            return null;
        }

        try {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => $vapidSubject,
                    'publicKey' => $vapidPublicKey,
                    'privateKey' => $vapidPrivateKey,
                ],
            ]);

            // Automatically flush after each notification
            $this->webPush->setAutomaticPadding(false);

            return $this->webPush;
        } catch (\Exception $e) {
            Log::error('Failed to initialize WebPush', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send push notification to a single subscription
     */
    public function sendToSubscription(PushSubscription $subscription, array $payload): bool
    {
        $webPush = $this->getWebPush();

        if (!$webPush) {
            return false;
        }

        try {
            $pushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->p256dh_key,
                'authToken' => $subscription->auth_key,
            ]);

            $report = $webPush->sendOneNotification(
                $pushSubscription,
                json_encode($payload)
            );

            if ($report->isSuccess()) {
                Log::info('Push notification sent successfully', [
                    'user_id' => $subscription->user_id,
                    'title' => $payload['title'] ?? 'Ministrify',
                ]);
                $subscription->touchLastUsed();
                return true;
            }

            Log::warning('Push notification failed', [
                'user_id' => $subscription->user_id,
                'reason' => $report->getReason(),
                'response' => $report->getResponse()?->getBody()?->getContents(),
            ]);

            // Deactivate expired/invalid subscriptions
            if ($report->isSubscriptionExpired()) {
                $subscription->deactivate();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            if ($this->isSubscriptionInvalid($e)) {
                $subscription->deactivate();
            }

            return false;
        }
    }

    /**
     * Send push notification to a user (all their subscriptions)
     */
    public function sendToUser(User $user, array $payload): int
    {
        $subscriptions = $user->pushSubscriptions()->active()->get();
        $sent = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Send push notifications to multiple users in batch
     */
    public function sendToUsers(array $userIds, array $payload): int
    {
        $webPush = $this->getWebPush();

        if (!$webPush) {
            return 0;
        }

        $subscriptions = PushSubscription::whereIn('user_id', $userIds)
            ->active()
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $payloadJson = json_encode($payload);

        // Queue all notifications
        foreach ($subscriptions as $subscription) {
            try {
                $pushSubscription = Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->p256dh_key,
                    'authToken' => $subscription->auth_key,
                ]);

                $webPush->queueNotification($pushSubscription, $payloadJson);
            } catch (\Exception $e) {
                Log::warning('Failed to queue notification', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send all queued notifications
        $sent = 0;
        $subscriptionMap = $subscriptions->keyBy('endpoint');

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();

            if ($report->isSuccess()) {
                $sent++;
                if (isset($subscriptionMap[$endpoint])) {
                    $subscriptionMap[$endpoint]->touch();
                }
            } else {
                Log::warning('Batch push notification failed', [
                    'endpoint' => $endpoint,
                    'reason' => $report->getReason(),
                ]);

                if ($report->isSubscriptionExpired() && isset($subscriptionMap[$endpoint])) {
                    $subscriptionMap[$endpoint]->deactivate();
                }
            }
        }

        Log::info('Batch push notifications sent', [
            'total' => $subscriptions->count(),
            'sent' => $sent,
        ]);

        return $sent;
    }

    /**
     * Send notification about assignment
     */
    public function notifyAssignment(User $user, string $eventTitle, string $position, string $date): bool
    {
        return $this->sendToUser($user, [
            'title' => 'Нове призначення',
            'body' => "Вас призначено на позицію \"{$position}\" для події \"{$eventTitle}\" ({$date})",
            'url' => '/my-schedule',
            'icon' => '/icons/icon-192x192.png',
            'tag' => 'assignment',
        ]) > 0;
    }

    /**
     * Send notification about assignment response
     */
    public function notifyAssignmentResponse(User $leader, string $personName, string $eventTitle, string $status): bool
    {
        $statusText = $status === 'confirmed' ? 'прийняв' : 'відхилив';

        return $this->sendToUser($leader, [
            'title' => 'Відповідь на призначення',
            'body' => "{$personName} {$statusText} призначення на подію \"{$eventTitle}\"",
            'url' => '/schedule',
            'icon' => '/icons/icon-192x192.png',
            'tag' => 'assignment-response',
        ]) > 0;
    }

    /**
     * Send reminder notification
     */
    public function notifyReminder(User $user, string $eventTitle, string $time): bool
    {
        return $this->sendToUser($user, [
            'title' => 'Нагадування',
            'body' => "Подія \"{$eventTitle}\" розпочнеться о {$time}",
            'url' => '/my-schedule',
            'icon' => '/icons/icon-192x192.png',
            'tag' => 'reminder-' . md5($eventTitle . $time),
        ]) > 0;
    }

    /**
     * Send announcement notification to multiple users
     */
    public function notifyAnnouncement(array $userIds, string $title, string $body, ?string $url = null): int
    {
        return $this->sendToUsers($userIds, [
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/announcements',
            'icon' => '/icons/icon-192x192.png',
            'tag' => 'announcement-' . time(),
        ]);
    }

    /**
     * Send custom notification
     */
    public function sendCustomNotification(User $user, string $title, string $body, ?string $url = null, ?string $tag = null): bool
    {
        return $this->sendToUser($user, [
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/dashboard',
            'icon' => '/icons/icon-192x192.png',
            'tag' => $tag ?? 'custom-' . time(),
        ]) > 0;
    }

    /**
     * Check if subscription is invalid (expired/unsubscribed)
     */
    private function isSubscriptionInvalid(\Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, '410') // Gone
            || str_contains($message, '404') // Not Found
            || str_contains($message, 'unsubscribed')
            || str_contains($message, 'expired');
    }

    /**
     * Get VAPID public key for client
     */
    public function getVapidPublicKey(): ?string
    {
        return config('services.vapid.public_key');
    }

    /**
     * Generate new VAPID keys (for setup)
     */
    public static function generateVapidKeys(): array
    {
        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
        return [
            'public_key' => $keys['publicKey'],
            'private_key' => $keys['privateKey'],
        ];
    }
}
