<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardCardComment extends Model
{
    protected $fillable = [
        'card_id',
        'user_id',
        'content',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(BoardCard::class, 'card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
