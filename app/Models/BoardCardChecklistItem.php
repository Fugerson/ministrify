<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardCardChecklistItem extends Model
{
    protected $fillable = [
        'card_id',
        'title',
        'is_completed',
        'position',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(BoardCard::class, 'card_id');
    }

    public function toggle(): void
    {
        $this->update(['is_completed' => !$this->is_completed]);
    }
}
