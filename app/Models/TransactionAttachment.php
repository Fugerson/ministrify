<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TransactionAttachment extends Model
{
    protected $fillable = [
        'transaction_id',
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected $appends = [
        'url',
        'is_image',
        'formatted_size',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    public function deleteFile(): bool
    {
        if ($this->path && Storage::exists($this->path)) {
            return Storage::delete($this->path);
        }

        return false;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (TransactionAttachment $attachment) {
            $attachment->deleteFile();
        });
    }
}
