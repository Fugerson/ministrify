<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SermonSeries extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sermon_series';

    protected $fillable = [
        'church_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'start_date',
        'end_date',
        'is_public',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($series) {
            if (empty($series->slug)) {
                $series->slug = Str::slug($series->title);
            }
        });
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class)->orderByDesc('sermon_date');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('start_date');
    }

    public function getSermonCountAttribute(): int
    {
        return $this->sermons()->public()->count();
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? \Storage::url($this->thumbnail) : null;
    }
}
