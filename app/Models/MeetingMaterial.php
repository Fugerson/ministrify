<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingMaterial extends Model
{
    protected $fillable = [
        'meeting_id',
        'title',
        'type',
        'content',
        'description',
        'sort_order',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(MinistryMeeting::class, 'meeting_id');
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'link' => '🔗',
            'file' => '📄',
            'note' => '📝',
            'video' => '🎬',
            'audio' => '🎵',
            'document' => '📑',
            default => '📎',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'link' => 'Посилання',
            'file' => 'Файл',
            'note' => 'Нотатка',
            'video' => 'Відео',
            'audio' => 'Аудіо',
            'document' => 'Документ',
            default => $this->type,
        };
    }
}
