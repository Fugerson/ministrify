<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class GroupGuest extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'group_guests';

    protected $fillable = [
        'group_id',
        'church_id',
        'first_name',
        'last_name',
        'photo',
        'birth_date',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

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
}
