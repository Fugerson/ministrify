<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class Resource extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'parent_id',
        'created_by',
        'name',
        'type',
        'file_path',
        'file_size',
        'mime_type',
        'icon',
        'description',
        'content',
    ];

    // Max file size: 10MB
    public const MAX_FILE_SIZE = 10 * 1024 * 1024;

    // Max total storage per church: 500MB
    public const MAX_CHURCH_STORAGE = 500 * 1024 * 1024;

    // Allowed file types
    public const ALLOWED_MIMES = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        // Audio
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
        'audio/ogg',
        // Video (small)
        'video/mp4',
        'video/webm',
        // Archives
        'application/zip',
        'application/x-rar-compressed',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Resource::class, 'parent_id')->orderByRaw("type = 'folder' DESC")->orderBy('name');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '—';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getIconAttribute(): string
    {
        if ($this->isFolder()) {
            return $this->attributes['icon'] ?? '📁';
        }

        if ($this->isDocument()) {
            return '📝';
        }

        // Determine icon based on mime type
        $mime = $this->mime_type ?? '';

        if (str_starts_with($mime, 'image/')) return '🖼️';
        if (str_starts_with($mime, 'audio/')) return '🎵';
        if (str_starts_with($mime, 'video/')) return '🎬';
        if (str_contains($mime, 'pdf')) return '📄';
        if (str_contains($mime, 'word') || str_contains($mime, 'document')) return '📝';
        if (str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet')) return '📊';
        if (str_contains($mime, 'powerpoint') || str_contains($mime, 'presentation')) return '📽️';
        if (str_contains($mime, 'zip') || str_contains($mime, 'rar')) return '🗜️';

        return '📎';
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $current = $this;

        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    public static function getChurchUsage(int $churchId): int
    {
        return static::where('church_id', $churchId)
            ->where('type', 'file')
            ->sum('file_size') ?? 0;
    }

    public static function canUpload(int $churchId, int $fileSize): bool
    {
        $currentUsage = static::getChurchUsage($churchId);
        return ($currentUsage + $fileSize) <= static::MAX_CHURCH_STORAGE;
    }
}
