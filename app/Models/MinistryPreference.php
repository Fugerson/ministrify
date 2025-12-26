<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinistryPreference extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'scheduling_preference_id',
        'ministry_id',
        'max_times_per_month',
        'preferred_times_per_month',
    ];

    public function schedulingPreference(): BelongsTo
    {
        return $this->belongsTo(SchedulingPreference::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }
}
