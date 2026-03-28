<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $fillable = [
        'hash',
        'message',
        'exception_class',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'user_id',
        'ip',
        'user_agent',
        'occurrences',
        'status',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('status', 'unresolved');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeIgnored($query)
    {
        return $query->where('status', 'ignored');
    }

    public function getShortFileAttribute(): string
    {
        if (! $this->file) {
            return '';
        }

        return str_replace('/var/www/html/', '', $this->file);
    }

    public function getShortMessageAttribute(): string
    {
        return mb_strlen($this->message) > 120
            ? mb_substr($this->message, 0, 120) . '…'
            : $this->message;
    }
}
