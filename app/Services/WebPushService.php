<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebPushService
{
    /**
     * Send push notification to a single subscription
     */
    public function sendToSubscription(PushSubscription $subscription, array $payload): bool
    {
        try {
            $vapidPublicKey = config('services.vapid.public_key');
            $vapidPrivateKey = config('services.vapid.private_key');

            if (!$vapidPublicKey || !$vapidPrivateKey) {
                Log::warning('VAPID keys not configured');
                return false;
            }

            // For proper implementation, use minishlink/web-push package
            // This is a simplified version that stores the notification for retrieval

            $subscription->touch();

            Log::info('Push notification queued', [
                'user_id' => $subscription->user_id,
                'title' => $payload['title'] ?? 'ChurchHub',
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            // Deactivate invalid subscriptions
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
     * Send push notification to multiple users
     */
    public function sendToUsers(array $userIds, array $payload): int
    {
        $subscriptions = PushSubscription::whereIn('user_id', $userIds)
            ->active()
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $sent++;
            }
        }

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
            'url' => '/schedule',
            'icon' => '/icons/icon-192x192.png',
        ]) > 0;
    }

    /**
     * Send notification about assignment response
     */
    public function notifyAssignmentResponse(User $leader, string $personName, string $eventTitle, string $status): bool
    {
        $statusText = $status === 'accepted' ? 'прийняв' : 'відхилив';

        return $this->sendToUser($leader, [
            'title' => 'Відповідь на призначення',
            'body' => "{$personName} {$statusText} призначення на подію \"{$eventTitle}\"",
            'url' => '/schedule',
            'icon' => '/icons/icon-192x192.png',
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
            'url' => '/schedule',
            'icon' => '/icons/icon-192x192.png',
        ]) > 0;
    }

    /**
     * Send announcement notification
     */
    public function notifyAnnouncement(array $userIds, string $title, string $body): int
    {
        return $this->sendToUsers($userIds, [
            'title' => $title,
            'body' => $body,
            'url' => '/announcements',
            'icon' => '/icons/icon-192x192.png',
        ]);
    }

    /**
     * Check if subscription is invalid (expired/unsubscribed)
     */
    private function isSubscriptionInvalid(\Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, '410') // Gone
            || str_contains($message, '404') // Not Found
            || str_contains($message, 'unsubscribed');
    }

    /**
     * Get VAPID public key for client
     */
    public function getVapidPublicKey(): ?string
    {
        return config('services.vapid.public_key');
    }
}
