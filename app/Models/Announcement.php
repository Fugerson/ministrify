<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'author_id',
        'title',
        'content',
        'is_pinned',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function readByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_reads')
            ->withPivot('read_at');
    }

    public function isReadBy(User $user): bool
    {
        return $this->readByUsers()->where('user_id', $user->id)->exists();
    }

    public function markAsReadBy(User $user): void
    {
        if (!$this->isReadBy($user)) {
            $this->readByUsers()->attach($user->id, ['read_at' => now()]);
        }
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForChurch($query, $churchId)
    {
        return $query->where('church_id', $churchId);
    }

    public static function unreadCount(int $churchId, int $userId): int
    {
        return static::forChurch($churchId)
            ->published()
            ->whereDoesntHave('readByUsers', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->count();
    }
}
