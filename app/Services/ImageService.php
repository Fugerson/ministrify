<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Process and store an uploaded image as WebP
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxWidth
     * @param int $quality
     * @return string Path to stored file
     */
    public function store(UploadedFile $file, string $directory, int $maxWidth = 800, int $quality = 85): string
    {
        // Generate unique filename with .webp extension
        $filename = uniqid() . '_' . time() . '.webp';
        $path = $directory . '/' . $filename;

        // Read and process image
        $image = Image::read($file);

        // Auto-orient based on EXIF data (must be before resize)
        $image->orient();

        // Resize if wider than max width (maintain aspect ratio)
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        // Encode as WebP
        $encoded = $image->toWebp($quality);

        // Store the file
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Process and store a profile photo (square crop)
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $size
     * @param int $quality
     * @return string Path to stored file
     */
    public function storeProfilePhoto(UploadedFile $file, string $directory, int $size = 400, int $quality = 85): string
    {
        $filename = uniqid() . '_' . time() . '.webp';
        $path = $directory . '/' . $filename;

        $image = Image::read($file);
        $image->orient();

        // Cover crop to square
        $image->cover($size, $size);

        $encoded = $image->toWebp($quality);
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Delete an old image if it exists
     *
     * @param string|null $path
     * @return void
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
