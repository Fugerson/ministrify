<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect();

        // Landing pages
        $landingPages = [
            ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => url('/features'), 'changefreq' => 'monthly', 'priority' => '0.9'],
            ['loc' => url('/register-church'), 'changefreq' => 'monthly', 'priority' => '0.9'],
            ['loc' => url('/faq'), 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => url('/docs'), 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => url('/contact'), 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => url('/terms'), 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['loc' => url('/privacy'), 'changefreq' => 'yearly', 'priority' => '0.5'],
        ];

        foreach ($landingPages as $page) {
            $urls->push($page);
        }

        // Public churches
        $churches = Church::where('public_site_enabled', true)->get();

        foreach ($churches as $church) {
            $urls->push([
                'loc' => route('public.church', $church->slug),
                'lastmod' => $church->updated_at->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);

            // Events listing
            $urls->push([
                'loc' => route('public.events', $church->slug),
                'changefreq' => 'daily',
                'priority' => '0.7',
            ]);

            // Individual public events (future only)
            $events = Event::where('church_id', $church->id)
                ->where('is_public', true)
                ->where('date', '>=', now()->startOfDay())
                ->get();

            foreach ($events as $event) {
                $urls->push([
                    'loc' => route('public.event', [$church->slug, $event]),
                    'lastmod' => $event->updated_at->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ]);
            }

            // Public ministries
            $ministries = Ministry::where('church_id', $church->id)
                ->where('is_public', true)
                ->get();

            foreach ($ministries as $ministry) {
                $urls->push([
                    'loc' => route('public.ministry', [$church->slug, $ministry->slug]),
                    'lastmod' => $ministry->updated_at->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ]);
            }

            // Public groups
            $groups = Group::where('church_id', $church->id)
                ->where('is_public', true)
                ->get();

            foreach ($groups as $group) {
                $urls->push([
                    'loc' => route('public.group', [$church->slug, $group->slug]),
                    'lastmod' => $group->updated_at->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ]);
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            if (isset($url['lastmod'])) {
                $xml .= "        <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            }
            $xml .= "        <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "        <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
