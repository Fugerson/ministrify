<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'people';

    protected $fillable = [
        'church_id',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'telegram_username',
        'telegram_chat_id',
        'photo',
        'address',
        'birth_date',
        'joined_date',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'joined_date' => 'date',
    ];

    protected $appends = ['full_name'];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'person_tag');
    }

    public function ministries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_person')
            ->withPivot('position_ids')
            ->withTimestamps();
    }

    public function leadingMinistries(): HasMany
    {
        return $this->hasMany(Ministry::class, 'leader_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(UnavailableDate::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isAvailableOn(\DateTime $date): bool
    {
        return !$this->unavailableDates()
            ->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->exists();
    }

    public function hasPositionInMinistry(Ministry $ministry, Position $position): bool
    {
        $pivot = $this->ministries()->where('ministry_id', $ministry->id)->first()?->pivot;

        if (!$pivot || !$pivot->position_ids) {
            return false;
        }

        $positionIds = is_array($pivot->position_ids)
            ? $pivot->position_ids
            : json_decode($pivot->position_ids, true);

        return in_array($position->id, $positionIds ?? []);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeWithTag($query, int $tagId)
    {
        return $query->whereHas('tags', fn($q) => $q->where('tags.id', $tagId));
    }

    public function scopeInMinistry($query, int $ministryId)
    {
        return $query->whereHas('ministries', fn($q) => $q->where('ministries.id', $ministryId));
    }
}
