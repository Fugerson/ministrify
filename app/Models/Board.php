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
