<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'ministry_id',
        'name',
        'sort_order',
    ];

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function getAvailablePeople(): Collection
    {
        if (! $this->ministry) {
            return new Collection;
        }

        return $this->ministry->members()
            ->get()
            ->filter(fn ($person) => $person->hasPositionInMinistry($this->ministry, $this));
    }
}
