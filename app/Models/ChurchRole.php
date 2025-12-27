<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChurchRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'name',
        'slug',
        'color',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Default roles to seed for new churches
    public const DEFAULT_ROLES = [
        ['name' => 'Член церкви', 'slug' => 'member', 'color' => '#6b7280', 'sort_order' => 1, 'is_default' => true],
        ['name' => 'Служитель', 'slug' => 'servant', 'color' => '#3b82f6', 'sort_order' => 2],
        ['name' => 'Лідер служіння', 'slug' => 'ministry-leader', 'color' => '#ec4899', 'sort_order' => 3],
        ['name' => 'Диякон', 'slug' => 'deacon', 'color' => '#8b5cf6', 'sort_order' => 4],
        ['name' => 'Пресвітер', 'slug' => 'presbyter', 'color' => '#f59e0b', 'sort_order' => 5],
        ['name' => 'Пастор', 'slug' => 'pastor', 'color' => '#10b981', 'sort_order' => 6],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public static function createDefaultsForChurch(int $churchId): void
    {
        foreach (self::DEFAULT_ROLES as $role) {
            self::create(array_merge($role, ['church_id' => $churchId]));
        }
    }
}
