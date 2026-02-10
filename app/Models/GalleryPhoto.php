<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class GalleryPhoto extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'gallery_id',
        'file_path',
        'thumbnail_path',
        'caption',
        'alt_text',
        'width',
        'height',
        'file_size',
        'sort_order',
        'is_cover',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function getFileUrlAttribute(): string
    {
        return \Storage::url($this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail_path
            ? \Storage::url($this->thumbnail_path)
            : $this->file_url;
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) return '-';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getAspectRatioAttribute(): ?string
    {
        if (!$this->width || !$this->height) return null;

        $a = $this->width;
        $b = $this->height;
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        return ($this->width / $a) . ':' . ($this->height / $a);
    }
}
