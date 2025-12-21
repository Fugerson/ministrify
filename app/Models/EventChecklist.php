<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventChecklist extends Model
{
    protected $fillable = [
        'event_id',
        'checklist_template_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EventChecklistItem::class)->orderBy('order');
    }

    public function getProgressAttribute(): int
    {
        $total = $this->items->count();
        if ($total === 0) return 100;

        $completed = $this->items->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }
}
