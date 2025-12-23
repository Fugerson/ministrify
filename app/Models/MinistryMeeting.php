<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinistryMeeting extends Model
{
    use Auditable;

    protected $fillable = [
        'ministry_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'location',
        'status',
        'theme',
        'notes',
        'summary',
        'copied_from_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function agendaItems(): HasMany
    {
        return $this->hasMany(MeetingAgendaItem::class, 'meeting_id')->orderBy('sort_order');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(MeetingMaterial::class, 'meeting_id')->orderBy('sort_order');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class, 'meeting_id');
    }

    public function copiedFrom(): BelongsTo
    {
        return $this->belongsTo(MinistryMeeting::class, 'copied_from_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planned' => 'Заплановано',
            'in_progress' => 'В процесі',
            'completed' => 'Завершено',
            'cancelled' => 'Скасовано',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'planned' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getConfirmedAttendeesCountAttribute(): int
    {
        return $this->attendees()->whereIn('status', ['confirmed', 'attended'])->count();
    }

    public function copyToNewMeeting(string $newDate): self
    {
        $newMeeting = $this->replicate(['id', 'created_at', 'updated_at', 'status', 'summary']);
        $newMeeting->date = $newDate;
        $newMeeting->status = 'planned';
        $newMeeting->copied_from_id = $this->id;
        $newMeeting->created_by = auth()->id();
        $newMeeting->save();

        // Copy agenda items
        foreach ($this->agendaItems as $item) {
            $newItem = $item->replicate(['id', 'created_at', 'updated_at', 'is_completed']);
            $newItem->meeting_id = $newMeeting->id;
            $newItem->is_completed = false;
            $newItem->save();
        }

        // Copy materials
        foreach ($this->materials as $material) {
            $newMaterial = $material->replicate(['id', 'created_at', 'updated_at']);
            $newMaterial->meeting_id = $newMeeting->id;
            $newMaterial->save();
        }

        return $newMeeting;
    }
}
