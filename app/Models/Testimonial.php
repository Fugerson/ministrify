<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'person_id',
        'author_name',
        'author_photo',
        'author_role',
        'title',
        'content',
        'video_url',
        'category',
        'is_public',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
    ];

    const CATEGORIES = [
        'salvation' => 'Спасіння',
        'healing' => 'Зцілення',
        'community' => 'Спільнота',
        'growth' => 'Духовний ріст',
        'provision' => 'Забезпечення',
        'other' => 'Інше',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category ?? 'Інше';
    }

    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->video_url) return null;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $this->video_url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }

    public function getAuthorPhotoUrlAttribute(): ?string
    {
        if ($this->author_photo) {
            return \Storage::url($this->author_photo);
        }
        if ($this->person?->photo) {
            return \Storage::url($this->person->photo);
        }
        return null;
    }
}
