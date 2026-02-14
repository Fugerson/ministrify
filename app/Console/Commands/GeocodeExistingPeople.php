<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Models\Person;
use App\Services\GeocodingService;
use Illuminate\Console\Command;

class GeocodeExistingPeople extends Command
{
    protected $signature = 'people:geocode {--limit=50 : Maximum people to geocode} {--church= : Limit to specific church ID} {--force : Re-geocode even if coordinates exist}';
    protected $description = 'Geocode addresses for existing people who have address but no coordinates';

    public function handle(GeocodingService $geocoding): int
    {
        $query = Person::whereNotNull('address')
            ->where('address', '!=', '');

        if (!$this->option('force')) {
            $query->whereNull('latitude');
        }

        if ($churchId = $this->option('church')) {
            $query->where('church_id', $churchId);
        }

        $people = $query->limit((int) $this->option('limit'))->get();

        if ($people->isEmpty()) {
            $this->info('No people to geocode.');
            return 0;
        }

        // Pre-load church cities for context
        $churchCities = Church::whereIn('id', $people->pluck('church_id')->unique())
            ->pluck('city', 'id')
            ->toArray();

        $this->info("Geocoding {$people->count()} people...");
        $bar = $this->output->createProgressBar($people->count());
        $successCount = 0;

        foreach ($people as $person) {
            $city = $churchCities[$person->church_id] ?? null;
            $result = $geocoding->geocode($person->address, $city);
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
