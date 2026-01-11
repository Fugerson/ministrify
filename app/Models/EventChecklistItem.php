<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventChecklistItem extends Model
{
    protected $fillable = [
        'event_checklist_id',
        'title',
        'description',
        'is_completed',
        'assigned_to',
        'completed_by',
        'completed_at',
        'order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(EventChecklist::class, 'event_checklist_id');
    }

    // Alias for checklist() - used in controllers
    public function eventChecklist(): BelongsTo
    {
        return $this->checklist();
    }

    public function assignedPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'assigned_to');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function markComplete(User $user): void
    {
        $this->update([
            'is_completed' => true,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);
    }

    public function markIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);
    }
}
