<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class Sermon extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'speaker_id',
        'created_by',
        'title',
        'slug',
        'description',
        'sermon_date',
        'thumbnail',
        'scripture_reference',
        'media_type',
        'youtube_url',
        'vimeo_url',
        'audio_file',
        'podcast_url',
        'notes_pdf',
        'slides_pdf',
        'sermon_series_id',
        'view_count',
        'duration_seconds',
        'is_public',
        'is_featured',
        'tags',
    ];

    protected $casts = [
        'sermon_date' => 'date',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($sermon) {
            if (empty($sermon->slug)) {
                $sermon->slug = Str::slug($sermon->title);
            }
        });
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'speaker_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(SermonSeries::class, 'sermon_series_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('sermon_date');
    }

    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->youtube_url) return null;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $this->youtube_url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getVimeoIdAttribute(): ?string
    {
        if (!$this->vimeo_url) return null;

        if (preg_match('/vimeo\.com\/(\d+)/', $this->vimeo_url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_seconds) return null;

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            return \Storage::url($this->thumbnail);
        }
        // Use YouTube thumbnail as fallback
        if ($this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/maxresdefault.jpg";
        }
        return null;
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function hasVideo(): bool
    {
        return $this->youtube_url || $this->vimeo_url;
    }

    public function hasAudio(): bool
    {
        return $this->audio_file || $this->podcast_url;
    }
}
