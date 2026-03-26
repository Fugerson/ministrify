<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportToExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    /**
     * @param  string  $exportClass  Fully qualified export class name
     * @param  array  $constructorArgs  Arguments for the export class constructor
     * @param  string  $filename  Output filename
     * @param  string  $disk  Storage disk for the output file
     */
    public function __construct(
        public string $exportClass,
        public array $constructorArgs,
        public string $filename,
        public string $disk = 'local',
    ) {
        $this->onQueue('exports');
    }

    public function handle(): void
    {
        try {
            $export = new $this->exportClass(...$this->constructorArgs);

            $path = 'exports/'.$this->filename;

            Excel::store($export, $path, $this->disk);

            Log::info('ExportToExcelJob: Completed', [
                'class' => $this->exportClass,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            Log::error('ExportToExcelJob: Failed', [
                'class' => $this->exportClass,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
