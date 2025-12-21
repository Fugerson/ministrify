<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnavailableDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'date_from',
        'date_to',
        'reason',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function coversDate(\DateTime $date): bool
    {
        return $date >= $this->date_from && $date <= $this->date_to;
    }
}
