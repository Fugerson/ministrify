<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'sender_id',
        'recipient_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get conversation between two users
     */
    public static function conversation(int $churchId, int $user1Id, int $user2Id)
    {
        return static::where('church_id', $churchId)
            ->where(function ($query) use ($user1Id, $user2Id) {
                $query->where(function ($q) use ($user1Id, $user2Id) {
                    $q->where('sender_id', $user1Id)->where('recipient_id', $user2Id);
                })->orWhere(function ($q) use ($user1Id, $user2Id) {
                    $q->where('sender_id', $user2Id)->where('recipient_id', $user1Id);
                });
            })
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all conversations for a user (latest message per conversation)
     */
    public static function conversationsForUser(int $churchId, int $userId)
    {
        return static::where('church_id', $churchId)
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('recipient_id', $userId);
            })
            ->with(['sender', 'recipient'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($message) use ($userId) {
                return $message->sender_id === $userId
                    ? $message->recipient_id
                    : $message->sender_id;
            })
            ->map(function ($messages) {
                return $messages->first();
            });
    }

    /**
     * Count unread messages for user
     */
    public static function unreadCount(?int $churchId, int $userId): int
    {
        if ($churchId === null) {
            return 0;
        }

        return static::where('church_id', $churchId)
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
