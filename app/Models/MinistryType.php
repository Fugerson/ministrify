<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinistryType extends Model
{
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

    public function ministries(): HasMany
    {
        return $this->hasMany(Ministry::class, 'type_id');
    }
}
