<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    protected $fillable = [
        'church_id',
        'name',
        'content',
        'type',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function parseContent(Person $person): string
    {
        $placeholders = [
            '{first_name}' => $person->first_name,
            '{last_name}' => $person->last_name,
            '{full_name}' => $person->full_name,
            '{phone}' => $person->phone ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $this->content);
    }
}
