<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public function geocode(string $address): ?array
    {
        if (empty(trim($address))) {
            return null;
        }

        $cacheKey = 'geocode:' . md5($address);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Rate limit: sleep 1 second between requests
            $lastRequestKey = 'geocode_last_request';
            $lastRequest = Cache::get($lastRequestKey, 0);
            $elapsed = microtime(true) - $lastRequest;
            if ($elapsed < 1.0) {
                usleep((int)((1.0 - $elapsed) * 1000000));
            }

            $response = Http::withHeaders([
                'User-Agent' => 'ChurchHub/1.0',
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

            Cache::put($lastRequestKey, microtime(true), 60);

            if ($response->successful() && $response->json()) {
                $data = $response->json()[0] ?? null;
                if ($data) {
                    $result = [
                        'lat' => (float) $data['lat'],
                        'lng' => (float) $data['lon'],
                    ];
                    Cache::put($cacheKey, $result, now()->addDays(30));
                    return $result;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Geocoding failed for address: ' . $address, ['error' => $e->getMessage()]);
            return null;
        }
    }
}
