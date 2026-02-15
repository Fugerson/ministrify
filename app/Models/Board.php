<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Board extends Model
{
    use Auditable;
    protected $fillable = [
        'church_id',
        'ministry_id',
        'name',
        'description',
        'color',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function isChurchWide(): bool
    {
        return $this->ministry_id === null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->ministry?->name ?? $this->name;
    }

    public function getDisplayColorAttribute(): string
    {
        return $this->ministry?->color ?? $this->color ?? '#3b82f6';
    }

    public function canAccess(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        if ($this->church_id !== $user->church_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($this->isChurchWide()) {
            return true;
        }

        return $this->ministry && $this->ministry->isMember($user);
    }

    public function scopeAccessibleBy($query, User $user)
    {
        $query->where('church_id', $user->church_id)
              ->where('is_archived', false);

        if ($user->isAdmin()) {
            return $query;
        }

        $personId = $user->person?->id;

        return $query->where(function ($q) use ($user, $personId) {
            // Church-wide boards
            $q->whereNull('ministry_id');

            if ($personId) {
                // Ministry boards where user is member
                $q->orWhereHas('ministry', function ($mq) use ($personId) {
                    $mq->where('leader_id', $personId)
                        ->orWhereHas('members', fn($pq) => $pq->where('person_id', $personId));
                });
            }
        });
    }

    public function columns(): HasMany
    {
        return $this->hasMany(BoardColumn::class)->orderBy('position');
    }

    public function epics(): HasMany
    {
        return $this->hasMany(BoardEpic::class)->orderBy('position');
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(BoardCard::class, BoardColumn::class, 'board_id', 'column_id');
    }

    public function getCardCountAttribute(): int
    {
        return $this->cards()->count();
    }

    public function getCompletedCardCountAttribute(): int
    {
        return $this->cards()->where('is_completed', true)->count();
    }

    public function getProgressAttribute(): int
    {
        $total = $this->card_count;
        if ($total === 0) return 0;
        return (int) round(($this->completed_card_count / $total) * 100);
    }
}
