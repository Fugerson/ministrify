<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class Faq extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'question',
        'answer',
        'category',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    const CATEGORIES = [
        'visiting' => 'Відвідування',
        'membership' => 'Членство',
        'giving' => 'Пожертви',
        'ministries' => 'Служіння',
        'beliefs' => 'Віровчення',
        'general' => 'Загальне',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('question');
    }

    public function scopeByCategory($query, ?string $category)
    {
        if (!$category) return $query;
        return $query->where('category', $category);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category ?? 'Загальне';
    }
}
