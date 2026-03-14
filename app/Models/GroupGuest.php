<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupGuest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id',
        'church_id',
        'first_name',
        'last_name',
        'photo',
        'age',
        'notes',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function attendances(): BelongsToMany
    {
        return $this->belongsToMany(Attendance::class, 'group_guest_attendance')
            ->withPivot('present')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getInitialsAttribute(): string
    {
        return mb_substr($this->first_name, 0, 1) . mb_substr($this->last_name ?? '', 0, 1);
    }
}
