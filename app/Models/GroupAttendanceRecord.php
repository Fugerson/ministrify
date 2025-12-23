<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupAttendanceRecord extends Model
{
    protected $fillable = [
        'group_attendance_id',
        'person_id',
        'present',
        'checked_in_at',
        'notes',
    ];

    protected $casts = [
        'present' => 'boolean',
        'checked_in_at' => 'datetime:H:i',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(GroupAttendance::class, 'group_attendance_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
