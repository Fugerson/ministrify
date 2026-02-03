<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_key',
        'content_encoding',
        'user_agent',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get subscription data for web-push library
     */
    public function getSubscriptionData(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->p256dh_key,
                'auth' => $this->auth_key,
            ],
            'contentEncoding' => $this->content_encoding,
        ];
    }

    /**
     * Update last used timestamp
     */
    public function touchLastUsed(): bool
    {
        $this->last_used_at = now();
        return $this->save();
    }

    /**
     * Deactivate this subscription
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
