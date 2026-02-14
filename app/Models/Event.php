<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'created_by',
        'ministry_id',
        'title',
        'date',
        'end_date',
        'time',
        'end_time',
        'notes',
        'recurrence_rule',
        'parent_event_id',
        'is_public',
        'allow_registration',
        'registration_limit',
        'registration_deadline',
        'public_description',
        'location',
        'cover_image',
        'checkin_token',
        'qr_checkin_enabled',
        'track_attendance',
        'is_service',
        'service_type',
        'reminder_settings',
        'google_event_id',
        'google_calendar_id',
        'google_synced_at',
        'google_sync_status',
    ];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
        'time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_public' => 'boolean',
        'is_service' => 'boolean',
        'allow_registration' => 'boolean',
        'registration_deadline' => 'datetime',
        'qr_checkin_enabled' => 'boolean',
        'track_attendance' => 'boolean',
        'reminder_settings' => 'array',
        'recurrence_rule' => 'array',
        'google_synced_at' => 'datetime',
        'google_sync_status' => 'string',
    ];

    // Service types
    const SERVICE_SUNDAY = 'sunday_service';
    const SERVICE_YOUTH = 'youth_meeting';
    const SERVICE_PRAYER = 'prayer_meeting';
    const SERVICE_SPECIAL = 'special_service';

    public static function serviceTypeLabels(): array
    {
        return [
            self::SERVICE_SUNDAY => 'Недільне служіння',
            self::SERVICE_YOUTH => 'Молодіжна зустріч',
            self::SERVICE_PRAYER => 'Молитовна зустріч',
            self::SERVICE_SPECIAL => 'Особливе служіння',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    /**
     * Get display name for ministry
     */
    public function getMinistryDisplayNameAttribute(): ?string
    {
        return $this->ministry?->name;
    }

    /**
     * Get ministry color
     */
    public function getMinistryDisplayColorAttribute(): ?string
    {
        return $this->ministry?->color;
    }

    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    public function childEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function responsibilities(): HasMany
    {
        return $this->hasMany(EventResponsibility::class)->orderBy('id');
    }

    public function attendance(): MorphOne
    {
        return $this->morphOne(Attendance::class, 'attendable');
    }

    public function checklist(): HasOne
    {
        return $this->hasOne(EventChecklist::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function planItems(): HasMany
    {
        return $this->hasMany(ServicePlanItem::class)->ordered();
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'event_songs')
            ->withPivot(['id', 'order', 'key', 'notes'])
            ->withTimestamps()
            ->orderBy('event_songs.order');
    }

    public function worshipTeam(): HasMany
    {
        return $this->hasMany(EventWorshipTeam::class);
    }

    public function ministryTeams(): HasMany
    {
        return $this->hasMany(EventMinistryTeam::class);
    }

    public function hasServicePlan(): bool
    {
        return $this->is_service && $this->planItems()->exists();
    }

    public function getServiceTypeLabelAttribute(): ?string
    {
        if (!$this->service_type) {
            return null;
        }
        return self::serviceTypeLabels()[$this->service_type] ?? $this->service_type;
    }

    public function getTotalPlanDurationAttribute(): int
    {
        return $this->planItems->sum('duration_minutes') ?? 0;
    }

    public function duplicatePlanFrom(Event $source): void
    {
        foreach ($source->planItems as $item) {
            $this->planItems()->create([
                'title' => $item->title,
                'description' => $item->description,
                'type' => $item->type,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'sort_order' => $item->sort_order,
                'notes' => $item->notes,
                'status' => ServicePlanItem::STATUS_PLANNED,
                // responsible_id is not copied - needs to be assigned fresh
            ]);
        }
    }

    public function getConfirmedRegistrationsCountAttribute(): int
    {
        return $this->registrations()
            ->whereIn('status', ['confirmed', 'attended'])
            ->sum(\DB::raw('1 + COALESCE(guests, 0)'));
    }

    public function getRemainingSpacesAttribute(): ?int
    {
        if (!$this->registration_limit) {
            return null;
        }
        return max(0, $this->registration_limit - $this->confirmed_registrations_count);
    }

    public function canAcceptRegistrations(): bool
    {
        if (!$this->allow_registration) {
            return false;
        }

        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }

        if ($this->date->isPast()) {
            return false;
        }

        if ($this->registration_limit && $this->remaining_spaces <= 0) {
            return false;
        }

        return true;
    }

    public function getFilledPositionsCountAttribute(): int
    {
        return $this->assignments()->count();
    }

    public function getTotalPositionsCountAttribute(): int
    {
        return $this->ministry?->positions()->count() ?? 0;
    }

    public function getUnfilledPositionsAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->ministry) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        $filledPositionIds = $this->assignments()->pluck('position_id')->toArray();

        return $this->ministry->positions()
            ->whereNotIn('id', $filledPositionIds)
            ->get();
    }

    public function getConfirmedAssignmentsCountAttribute(): int
    {
        return $this->assignments()->where('status', 'confirmed')->count();
    }

    public function getPendingAssignmentsCountAttribute(): int
    {
        return $this->assignments()->where('status', 'pending')->count();
    }

    public function isFullyStaffed(): bool
    {
        return $this->unfilled_positions->isEmpty();
    }

    public function getDateTimeAttribute(): ?\DateTime
    {
        if (!$this->date) {
            return null;
        }

        if (!$this->time) {
            return \DateTime::createFromFormat('Y-m-d', $this->date->format('Y-m-d'));
        }

        return \DateTime::createFromFormat(
            'Y-m-d H:i',
            $this->date->format('Y-m-d') . ' ' . $this->time->format('H:i')
        );
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->orderBy('time');
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeForWeek($query, $startOfWeek)
    {
        return $query->whereBetween('date', [
            $startOfWeek,
            $startOfWeek->copy()->endOfWeek()
        ]);
    }

    public function scopeServices($query)
    {
        return $query->where('is_service', true);
    }

    public function scopeOfServiceType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    // ==================
    // QR Check-in Methods
    // ==================

    /**
     * Generate a unique check-in token for QR code
     */
    public function generateCheckinToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $this->update(['checkin_token' => $token]);
        return $token;
    }

    /**
     * Get or generate check-in token
     */
    public function getOrCreateCheckinToken(): string
    {
        if (!$this->checkin_token) {
            return $this->generateCheckinToken();
        }
        return $this->checkin_token;
    }

    /**
     * Get the QR check-in URL
     */
    public function getCheckinUrlAttribute(): string
    {
        $token = $this->getOrCreateCheckinToken();
        return url("/checkin/{$token}");
    }

    /**
     * Check if QR check-in is available for this event
     */
    public function canQrCheckin(): bool
    {
        if (!$this->qr_checkin_enabled) {
            return false;
        }

        // Allow check-in on the event day or day before
        $today = now()->startOfDay();
        $eventDay = $this->date->copy()->startOfDay();

        return $today->diffInDays($eventDay, false) >= 0 && $today->diffInDays($eventDay, false) <= 1;
    }

    /**
     * Find event by check-in token
     */
    public static function findByCheckinToken(string $token): ?self
    {
        return static::where('checkin_token', $token)->first();
    }

    /**
     * Check if event has reminders enabled
     */
    public function hasReminders(): bool
    {
        return !empty($this->reminder_settings);
    }

    /**
     * Get reminders for this event
     */
    public function getReminders(): array
    {
        return $this->reminder_settings ?? [];
    }

    /**
     * Check if reminder should be sent for given offset
     */
    public function shouldSendReminder(string $type, int $value): bool
    {
        if (!$this->hasReminders()) {
            return false;
        }

        foreach ($this->reminder_settings as $reminder) {
            if ($reminder['type'] === $type && $reminder['value'] == $value) {
                return true;
            }
        }

        return false;
    }
}
