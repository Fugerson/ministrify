<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupAttendance extends Model
{
    use Auditable;

    protected $fillable = [
        'group_id',
        'church_id',
        'date',
        'time',
        'location',
        'notes',
        'total_count',
        'members_present',
        'guests_count',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(GroupAttendanceRecord::class);
    }

    public function presentMembers()
    {
        return $this->records()->where('present', true);
    }

    public function getAttendanceRateAttribute(): float
    {
        $totalMembers = $this->group?->members()->count() ?? 0;
        if ($totalMembers === 0) return 0;

        return round(($this->members_present / $totalMembers) * 100, 1);
    }
}
