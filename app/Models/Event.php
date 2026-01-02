<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'title',
        'date',
        'time',
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
        'is_service',
        'service_type',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'is_public' => 'boolean',
        'is_service' => 'boolean',
        'allow_registration' => 'boolean',
        'registration_deadline' => 'datetime',
        'qr_checkin_enabled' => 'boolean',
    ];

    // Service types
    const SERVICE_SUNDAY = 'sunday_service';
    const SERVICE_YOUTH = 'youth_meeting';
    const SERVICE_PRAYER = 'prayer_meeting';
    const SERVICE_SPECIAL = 'special_service';

    public static function serviceTypeLabels(): array
    {
        return [
            self::SERVICE_SUNDAY => 'Воскресне служіння',
            self::SERVICE_YOUTH => 'Молодіжна зустріч',
            self::SERVICE_PRAYER => 'Молитовна зустріч',
            self::SERVICE_SPECIAL => 'Особливе служіння',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
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

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
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
            ->sum(\DB::raw('1 + guests'));
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
            return collect();
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

    public function getDateTimeAttribute(): \DateTime
    {
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
        $eventDay = $this->date->startOfDay();

        return $today->diffInDays($eventDay, false) >= -1 && $today->diffInDays($eventDay, false) <= 0;
    }

    /**
     * Find event by check-in token
     */
    public static function findByCheckinToken(string $token): ?self
    {
        return static::where('checkin_token', $token)->first();
    }
}
