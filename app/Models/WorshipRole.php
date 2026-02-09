<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorshipRole extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'name',
        'icon',
        'color',
        'sort_order',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function personSkills(): HasMany
    {
        return $this->hasMany(PersonWorshipSkill::class);
    }

    public function eventTeamMembers(): HasMany
    {
        return $this->hasMany(EventWorshipTeam::class);
    }

    /**
     * Get people who have this skill
     */
    public function skilledPeople()
    {
        return Person::whereHas('worshipSkills', function ($q) {
            $q->where('worship_role_id', $this->id);
        });
    }
}
