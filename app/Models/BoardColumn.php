<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardColumn extends Model
{
    protected $fillable = [
        'board_id',
        'name',
        'color',
        'position',
        'card_limit',
    ];

    protected $casts = [
        'card_limit' => 'integer',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(BoardCard::class, 'column_id')->orderBy('position');
    }

    public function getCardCountAttribute(): int
    {
        return $this->cards()->count();
    }

    public function isAtLimit(): bool
    {
        if ($this->card_limit === null) return false;
        return $this->card_count >= $this->card_limit;
    }
}
