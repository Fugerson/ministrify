<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardCardAttachment extends Model
{
    protected $fillable = [
        'card_id',
        'name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(BoardCard::class, 'card_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getSizeForHumansAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }
        return round($bytes, 2) . ' ' . $units[$index];
    }
}
