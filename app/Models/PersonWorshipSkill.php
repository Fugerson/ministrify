<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonWorshipSkill extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'person_id',
        'worship_role_id',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function worshipRole(): BelongsTo
    {
        return $this->belongsTo(WorshipRole::class);
    }
}
