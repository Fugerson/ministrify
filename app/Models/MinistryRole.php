<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinistryRole extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'ministry_id',
        'name',
        'icon',
        'color',
        'sort_order',
    ];

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function eventTeamMembers(): HasMany
    {
        return $this->hasMany(EventMinistryTeam::class);
    }
}
