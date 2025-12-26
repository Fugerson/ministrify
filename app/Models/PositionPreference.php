<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionPreference extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'scheduling_preference_id',
        'position_id',
        'max_times_per_month',
        'preferred_times_per_month',
    ];

    public function schedulingPreference(): BelongsTo
    {
        return $this->belongsTo(SchedulingPreference::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
