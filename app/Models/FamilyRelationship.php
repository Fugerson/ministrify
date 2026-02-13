<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class FamilyRelationship extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'person_id',
        'related_person_id',
        'relationship_type',
    ];

    public const TYPE_SPOUSE = 'spouse';
    public const TYPE_CHILD = 'child';
    public const TYPE_PARENT = 'parent';
    public const TYPE_SIBLING = 'sibling';

    public static function getTypes(): array
    {
        return [
            self::TYPE_SPOUSE => 'Чоловік/Дружина',
            self::TYPE_CHILD => 'Дитина',
            self::TYPE_PARENT => 'Батько/Мати',
            self::TYPE_SIBLING => 'Брат/Сестра',
        ];
    }

    public static function getInverseType(string $type): string
    {
        return match ($type) {
            self::TYPE_SPOUSE => self::TYPE_SPOUSE,
            self::TYPE_CHILD => self::TYPE_PARENT,
            self::TYPE_PARENT => self::TYPE_CHILD,
            self::TYPE_SIBLING => self::TYPE_SIBLING,
            default => $type,
        };
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function relatedPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'related_person_id');
    }

    public function getTypeLabel(): string
    {
        return self::getTypes()[$this->relationship_type] ?? $this->relationship_type;
    }
}
