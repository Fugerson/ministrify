<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    /**
     * @param  string  $storedPath  Path where raw upload was saved
     * @param  string  $outputDir  Directory for processed output (e.g. 'public/people')
     * @param  int  $maxWidth  Max width for resize
     * @param  int  $maxHeight  Max height for resize
     * @param  bool  $squareCrop  Whether to crop to square (profile photos)
     */
    public function __construct(
        public string $storedPath,
        public string $outputDir,
        public int $maxWidth = 800,
        public int $maxHeight = 800,
        public bool $squareCrop = false,
    ) {
        $this->onQueue('images');
    }

    public function handle(): void
    {
        try {
            $fullPath = Storage::disk('local')->path($this->storedPath);

            if (! file_exists($fullPath)) {
                Log::warning('ProcessImageJob: File not found', ['path' => $this->storedPath]);

                return;
            }

            $image = Image::read($fullPath);
            $image->orientate();

            if ($this->squareCrop) {
                $size = min($image->width(), $image->height());
                $image->cover($size, $size);
            }

            $image->scaleDown($this->maxWidth, $this->maxHeight);

            $filename = pathinfo($this->storedPath, PATHINFO_FILENAME).'.webp';
            $outputPath = $this->outputDir.'/'.$filename;

            Storage::disk('local')->put(
                $outputPath,
                $image->toWebp(85)->toString()
            );

            // Clean up the raw upload
            Storage::disk('local')->delete($this->storedPath);

            Log::info('ProcessImageJob: Completed', ['output' => $outputPath]);
        } catch (\Exception $e) {
            Log::error('ProcessImageJob: Failed', [
                'path' => $this->storedPath,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
