<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'church_id',
        'created_by',
        'title',
        'slug',
        'description',
        'cover_image',
        'event_date',
        'is_public',
        'sort_order',
        'photo_count',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_public' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($gallery) {
            if (empty($gallery->slug)) {
                $gallery->slug = Str::slug($gallery->title);
            }
        });
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('event_date');
    }

    public function getCoverPhotoUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return \Storage::url($this->cover_image);
        }

        $coverPhoto = $this->photos()->where('is_cover', true)->first()
            ?? $this->photos()->first();

        return $coverPhoto?->file_url;
    }

    public function updatePhotoCount(): void
    {
        $this->update(['photo_count' => $this->photos()->count()]);
    }
}
