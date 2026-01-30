<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class StaffMember extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'person_id',
        'name',
        'title',
        'role_category',
        'bio',
        'photo',
        'email',
        'phone',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'is_public',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
    ];

    const CATEGORIES = [
        'pastor' => 'Пастор',
        'staff' => 'Персонал',
        'elder' => 'Пресвітер',
        'deacon' => 'Диякон',
        'volunteer' => 'Волонтер',
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
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('role_category', $category);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->role_category] ?? $this->role_category;
    }

    public function hasSocialLinks(): bool
    {
        return $this->facebook_url || $this->instagram_url ||
               $this->twitter_url || $this->linkedin_url;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return \Storage::url($this->photo);
        }
        if ($this->person?->photo) {
            return \Storage::url($this->person->photo);
        }
        return null;
    }
}
