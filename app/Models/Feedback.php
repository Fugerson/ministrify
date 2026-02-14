<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'feedback';

    protected $fillable = [
        'church_id',
        'category',
        'message',
        'rating',
        'is_anonymous',
        'name',
        'email',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'rating' => 'integer',
    ];

    // Константи для категорій
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_SERMON = 'sermon';
    const CATEGORY_WORSHIP = 'worship';
    const CATEGORY_SUGGESTION = 'suggestion';
    const CATEGORY_COMPLAINT = 'complaint';

    // Константи для статусів
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';
    const STATUS_ARCHIVED = 'archived';

    // Категорії з українськими назвами
    const CATEGORIES = [
        'general' => 'Загальне',
        'sermon' => 'Проповідь',
        'worship' => 'Прославлення',
        'suggestion' => 'Пропозиція',
        'complaint' => 'Скарга',
    ];

    const STATUSES = [
        'new' => 'Новий',
        'read' => 'Прочитано',
        'archived' => 'Архів',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStarRatingDisplayAttribute(): string
    {
        if (!$this->rating) {
            return '—';
        }
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeRead($query)
    {
        return $query->where('status', self::STATUS_READ);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForChurch($query, int $churchId)
    {
        return $query->where('church_id', $churchId);
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNot('status', self::STATUS_ARCHIVED);
    }

    public function markAsRead(): void
    {
        if ($this->status === self::STATUS_NEW) {
            $this->update(['status' => self::STATUS_READ]);
        }
    }

    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    public function getSubmitterNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Анонімно';
        }
        return $this->name ?? 'Невідомо';
    }
}
