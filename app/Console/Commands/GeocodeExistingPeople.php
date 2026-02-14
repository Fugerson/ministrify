<?php

namespace App\Console\Commands;

use App\Models\Person;
use App\Services\GeocodingService;
use Illuminate\Console\Command;

class GeocodeExistingPeople extends Command
{
    protected $signature = 'people:geocode {--limit=50 : Maximum people to geocode} {--church= : Limit to specific church ID}';
    protected $description = 'Geocode addresses for existing people who have address but no coordinates';

    public function handle(GeocodingService $geocoding): int
    {
        $query = Person::whereNotNull('address')
            ->where('address', '!=', '')
            ->whereNull('latitude');

        if ($churchId = $this->option('church')) {
            $query->where('church_id', $churchId);
        }

        $people = $query->limit((int) $this->option('limit'))->get();

        if ($people->isEmpty()) {
            $this->info('No people to geocode.');
            return 0;
        }

        $this->info("Geocoding {$people->count()} people...");
        $bar = $this->output->createProgressBar($people->count());
        $successCount = 0;

        foreach ($people as $person) {
            $result = $geocoding->geocode($person->address);
            if ($result) {
                $person->update([
                    'latitude' => $result['lat'],
                    'longitude' => $result['lng'],
                ]);
                $successCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully geocoded {$successCount} of {$people->count()} people.");

        return 0;
    }
}
