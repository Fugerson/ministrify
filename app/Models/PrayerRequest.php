<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PrayerRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'church_id',
        'person_id',
        'user_id',
        'title',
        'description',
        'is_anonymous',
        'is_public',
        'is_urgent',
        'status',
        'answer_testimony',
        'answered_at',
        'prayer_count',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_public' => 'boolean',
        'is_urgent' => 'boolean',
        'answered_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_ANSWERED = 'answered';
    const STATUS_CLOSED = 'closed';

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prayedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'prayer_request_prayers')
            ->withPivot('prayed_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Анонімно';
        }
        return $this->person?->full_name ?? $this->user?->name ?? 'Невідомо';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Активне',
            self::STATUS_ANSWERED => 'Відповідь отримано',
            self::STATUS_CLOSED => 'Закрито',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'blue',
            self::STATUS_ANSWERED => 'green',
            self::STATUS_CLOSED => 'gray',
            default => 'gray',
        };
    }

    public function markAsPrayed(User $user): void
    {
        if (!$this->prayedBy()->where('user_id', $user->id)->exists()) {
            $this->prayedBy()->attach($user->id, ['prayed_at' => now()]);
            $this->increment('prayer_count');
        }
    }

    public function hasPrayed(User $user): bool
    {
        return $this->prayedBy()->where('user_id', $user->id)->exists();
    }

    public function markAsAnswered(?string $testimony = null): void
    {
        $this->update([
            'status' => self::STATUS_ANSWERED,
            'answered_at' => now(),
            'answer_testimony' => $testimony,
        ]);
    }
}
