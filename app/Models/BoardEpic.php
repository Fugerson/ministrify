<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardEpic extends Model
{
    protected $fillable = [
        'board_id',
        'name',
        'color',
        'description',
        'position',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(BoardCard::class, 'epic_id');
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
