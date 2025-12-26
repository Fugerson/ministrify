<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePlanItem extends Model
{
    use HasFactory, SoftDeletes;

    // Item types
    const TYPE_WORSHIP = 'worship';
    const TYPE_SERMON = 'sermon';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_PRAYER = 'prayer';
    const TYPE_OFFERING = 'offering';
    const TYPE_TESTIMONY = 'testimony';
    const TYPE_BAPTISM = 'baptism';
    const TYPE_COMMUNION = 'communion';
    const TYPE_CHILD_BLESSING = 'child_blessing';
    const TYPE_SPECIAL = 'special';
    const TYPE_OTHER = 'other';

    // Statuses
    const STATUS_PLANNED = 'planned';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';

    // Default durations in minutes by type
    const DEFAULT_DURATIONS = [
        self::TYPE_WORSHIP => 30,
        self::TYPE_SERMON => 40,
        self::TYPE_ANNOUNCEMENT => 10,
        self::TYPE_PRAYER => 10,
        self::TYPE_OFFERING => 5,
        self::TYPE_TESTIMONY => 10,
        self::TYPE_BAPTISM => 20,
        self::TYPE_COMMUNION => 15,
        self::TYPE_CHILD_BLESSING => 10,
        self::TYPE_SPECIAL => 15,
        self::TYPE_OTHER => 10,
    ];

    /**
     * Get default duration for a type
     */
    public static function getDefaultDuration(string $type): int
    {
        return self::DEFAULT_DURATIONS[$type] ?? 10;
    }

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'type',
        'start_time',
        'end_time',
        'responsible_id',
        'responsible_names',
        'sort_order',
        'notes',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get type labels in Ukrainian
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_WORSHIP => 'Прославлення',
            self::TYPE_SERMON => 'Проповідь',
            self::TYPE_ANNOUNCEMENT => 'Оголошення',
            self::TYPE_PRAYER => 'Молитва',
            self::TYPE_OFFERING => 'Пожертва',
            self::TYPE_TESTIMONY => 'Свідчення',
            self::TYPE_BAPTISM => 'Хрещення',
            self::TYPE_COMMUNION => 'Причастя',
            self::TYPE_CHILD_BLESSING => 'Дитяче благословення',
            self::TYPE_SPECIAL => 'Особливий номер',
            self::TYPE_OTHER => 'Інше',
        ];
    }

    /**
     * Get status labels in Ukrainian
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PLANNED => 'Заплановано',
            self::STATUS_CONFIRMED => 'Підтверджено',
            self::STATUS_COMPLETED => 'Виконано',
        ];
    }

    /**
     * Get type icons
     */
    public static function typeIcons(): array
    {
        return [
            self::TYPE_WORSHIP => 'music',
            self::TYPE_SERMON => 'book-open',
            self::TYPE_ANNOUNCEMENT => 'megaphone',
            self::TYPE_PRAYER => 'hands',
            self::TYPE_OFFERING => 'heart',
            self::TYPE_TESTIMONY => 'user-check',
            self::TYPE_BAPTISM => 'droplet',
            self::TYPE_COMMUNION => 'wine',
            self::TYPE_CHILD_BLESSING => 'baby',
            self::TYPE_SPECIAL => 'star',
            self::TYPE_OTHER => 'circle',
        ];
    }

    /**
     * Get type colors
     */
    public static function typeColors(): array
    {
        return [
            self::TYPE_WORSHIP => '#8b5cf6',      // Purple
            self::TYPE_SERMON => '#3b82f6',       // Blue
            self::TYPE_ANNOUNCEMENT => '#f59e0b', // Amber
            self::TYPE_PRAYER => '#10b981',       // Emerald
            self::TYPE_OFFERING => '#ef4444',     // Red
            self::TYPE_TESTIMONY => '#06b6d4',    // Cyan
            self::TYPE_BAPTISM => '#0ea5e9',      // Sky
            self::TYPE_COMMUNION => '#7c3aed',    // Violet
            self::TYPE_CHILD_BLESSING => '#ec4899', // Pink
            self::TYPE_SPECIAL => '#f97316',      // Orange
            self::TYPE_OTHER => '#6b7280',        // Gray
        ];
    }

    // Relationships

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'responsible_id');
    }

    // Accessors

    public function getTypeLabelAttribute(): ?string
    {
        if (!$this->type) {
            return null;
        }
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function getResponsibleDisplayAttribute(): ?string
    {
        // First check for free text responsible names
        if ($this->responsible_names) {
            return $this->responsible_names;
        }

        // Fall back to linked person
        if ($this->responsible) {
            return $this->responsible->full_name;
        }

        return null;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getTypeIconAttribute(): string
    {
        return self::typeIcons()[$this->type] ?? 'circle';
    }

    public function getTypeColorAttribute(): string
    {
        return self::typeColors()[$this->type] ?? '#6b7280';
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    public function getFormattedTimeRangeAttribute(): ?string
    {
        if (!$this->start_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time)->format('H:i');

        if (!$this->end_time) {
            return $start;
        }

        $end = Carbon::parse($this->end_time)->format('H:i');

        return "{$start} - {$end}";
    }

    public function getFormattedDurationAttribute(): ?string
    {
        $minutes = $this->duration_minutes;

        if (!$minutes) {
            return null;
        }

        if ($minutes < 60) {
            return "{$minutes} хв";
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return $mins > 0 ? "{$hours} год {$mins} хв" : "{$hours} год";
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('start_time');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', self::STATUS_PLANNED);
    }
}
