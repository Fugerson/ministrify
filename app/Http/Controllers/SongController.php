<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Csv\Reader;

class SongController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->canView('ministries'), 403);
        $church = $this->getCurrentChurch();

        // Load all songs for client-side filtering
        $songs = Song::where('church_id', $church->id)
            ->orderBy('title')
            ->get();

        // Get all unique tags for filter
        $allTags = Song::where('church_id', $church->id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        // Get all unique artists for filter
        $allArtists = Song::where('church_id', $church->id)
            ->whereNotNull('artist')
            ->where('artist', '!=', '')
            ->pluck('artist')
            ->unique()
            ->sort()
            ->values();

        return view('songs.index', compact('songs', 'allTags', 'allArtists'));
    }

    public function create()
    {
        abort_unless(auth()->user()->canCreate('ministries'), 403);
        $church = $this->getCurrentChurch();

        // Get existing artists for autocomplete
        $artists = Song::where('church_id', $church->id)
            ->whereNotNull('artist')
            ->distinct()
            ->pluck('artist')
            ->sort()
            ->values();

        // Get all existing tags
        $allTags = Song::where('church_id', $church->id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('songs.create', compact('artists', 'allTags'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->canCreate('ministries')) {
            abort(403);
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|numeric|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|url|max:255',
            'spotify_url' => 'nullable|url|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'new_tag' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
            'resource_links' => 'nullable|array|max:20',
            'resource_links.*.label' => 'required|string|max:255',
            'resource_links.*.url' => 'required|url|max:500',
        ]);

        $church = $this->getCurrentChurch();

        // Clean empty values
        $validated['youtube_url'] = ! empty($validated['youtube_url']) ? $validated['youtube_url'] : null;
        $validated['spotify_url'] = ! empty($validated['spotify_url']) ? $validated['spotify_url'] : null;
        $validated['bpm'] = ! empty($validated['bpm']) ? (int) $validated['bpm'] : null;

        // Filter out empty links
        $resourceLinks = collect($validated['resource_links'] ?? [])->filter(fn ($l) => ! empty($l['label']) && ! empty($l['url']))->values()->all();

        // Combine selected tags with new tag
        $tags = $validated['tags'] ?? [];
        if (! empty($validated['new_tag'])) {
            $newTags = array_map('trim', explode(',', $validated['new_tag']));
            $tags = array_merge($tags, array_filter($newTags));
        }
        $tags = array_unique(array_filter($tags));

        $song = Song::create([
            'church_id' => $church->id,
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'key' => $validated['key'],
            'bpm' => $validated['bpm'],
            'lyrics' => $validated['lyrics'],
            'chords' => $validated['chords'],
            'ccli_number' => $validated['ccli_number'],
            'youtube_url' => $validated['youtube_url'],
            'spotify_url' => $validated['spotify_url'],
            'tags' => ! empty($tags) ? array_values($tags) : null,
            'notes' => $validated['notes'],
            'resource_links' => ! empty($resourceLinks) ? $resourceLinks : null,
            'created_by' => auth()->id(),
        ]);

        broadcast(new \App\Events\ChurchDataUpdated($church->id, 'service-planning', 'updated'))->toOthers();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Пісню додано до бібліотеки.',
                'redirect_url' => route('songs.index'),
                'song' => [
                    'id' => $song->id,
                    'title' => $song->title,
                    'artist' => $song->artist,
                    'key' => $song->key,
                    'bpm' => $song->bpm,
                    'lyrics' => $song->lyrics,
                    'chords' => $song->chords,
                    'ccli_number' => $song->ccli_number,
                    'youtube_url' => $song->youtube_url,
                    'spotify_url' => $song->spotify_url,
                    'tags' => $song->tags ?? [],
                    'notes' => $song->notes,
                    'resource_links' => $song->resource_links ?? [],
                    'times_used' => $song->times_used ?? 0,
                    'created_at' => $song->created_at,
                ],
            ]);
        }

        return $this->successResponse($request, 'Пісню додано до бібліотеки.', 'songs.index');
    }

    public function show(Song $song)
    {
        $this->authorizeChurch($song);

        $song->load(['creator', 'events' => fn ($q) => $q->latest('date')->limit(10)]);

        return view('songs.show', compact('song'));
    }

    public function edit(Song $song)
    {
        $this->authorizeChurch($song);
        $church = $this->getCurrentChurch();

        // Get existing artists for autocomplete
        $artists = Song::where('church_id', $church->id)
            ->whereNotNull('artist')
            ->distinct()
            ->pluck('artist')
            ->sort()
            ->values();

        // Get all existing tags
        $allTags = Song::where('church_id', $church->id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('songs.edit', compact('song', 'artists', 'allTags'));
    }

    public function update(Request $request, Song $song)
    {
        $this->authorizeChurch($song);
        if (! auth()->user()->canEdit('ministries')) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|numeric|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|url|max:255',
            'spotify_url' => 'nullable|url|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'new_tag' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
            'resource_links' => 'nullable|array|max:20',
            'resource_links.*.label' => 'required|string|max:255',
            'resource_links.*.url' => 'required|url|max:500',
        ]);

        // Clean empty values
        $validated['youtube_url'] = ! empty($validated['youtube_url']) ? $validated['youtube_url'] : null;
        $validated['spotify_url'] = ! empty($validated['spotify_url']) ? $validated['spotify_url'] : null;
        $validated['bpm'] = ! empty($validated['bpm']) ? (int) $validated['bpm'] : null;

        // Filter out empty links
        $resourceLinks = collect($validated['resource_links'] ?? [])->filter(fn ($l) => ! empty($l['label']) && ! empty($l['url']))->values()->all();

        // Combine selected tags with new tag
        $tags = $validated['tags'] ?? [];
        if (! empty($validated['new_tag'])) {
            $newTags = array_map('trim', explode(',', $validated['new_tag']));
            $tags = array_merge($tags, array_filter($newTags));
        }
        $tags = array_unique(array_filter($tags));

        $song->update([
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'key' => $validated['key'],
            'bpm' => $validated['bpm'],
            'lyrics' => $validated['lyrics'],
            'chords' => $validated['chords'],
            'ccli_number' => $validated['ccli_number'],
            'youtube_url' => $validated['youtube_url'],
            'spotify_url' => $validated['spotify_url'],
            'tags' => ! empty($tags) ? array_values($tags) : null,
            'notes' => $validated['notes'],
            'resource_links' => ! empty($resourceLinks) ? $resourceLinks : null,
        ]);

        broadcast(new \App\Events\ChurchDataUpdated($song->church_id, 'service-planning', 'updated'))->toOthers();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Пісню оновлено.',
                'redirect_url' => route('songs.show', $song),
                'song' => [
                    'id' => $song->id,
                    'title' => $song->title,
                    'artist' => $song->artist,
                    'key' => $song->key,
                    'bpm' => $song->bpm,
                    'lyrics' => $song->lyrics,
                    'chords' => $song->chords,
                    'ccli_number' => $song->ccli_number,
                    'youtube_url' => $song->youtube_url,
                    'spotify_url' => $song->spotify_url,
                    'tags' => $song->tags ?? [],
                    'notes' => $song->notes,
                    'resource_links' => $song->resource_links ?? [],
                    'times_used' => $song->times_used ?? 0,
                    'created_at' => $song->created_at,
                ],
            ]);
        }

        return $this->successResponse($request, 'Пісню оновлено.', 'songs.show', [$song]);
    }

    public function moveTag(Request $request, Song $song)
    {
        $this->authorizeChurch($song);
        abort_unless(auth()->user()->canEdit('ministries'), 403);

        $validated = $request->validate([
            'from_tag' => 'nullable|string|max:50',
            'to_tag' => 'nullable|string|max:50',
        ]);

        $tags = $song->tags ?? [];

        // Remove old tag if present
        if (! empty($validated['from_tag'])) {
            $tags = array_values(array_filter($tags, fn ($t) => $t !== $validated['from_tag']));
        }

        // Add new tag if not already present
        if (! empty($validated['to_tag']) && ! in_array($validated['to_tag'], $tags)) {
            $tags[] = $validated['to_tag'];
        }

        $song->update([
            'tags' => ! empty($tags) ? array_values($tags) : null,
        ]);

        return response()->json([
            'success' => true,
            'tags' => $song->tags ?? [],
        ]);
    }

    public function destroy(Request $request, Song $song)
    {
        $this->authorizeChurch($song);
        if (! auth()->user()->canDelete('ministries')) {
            abort(403);
        }

        $churchId = $song->church_id;
        $song->delete();

        broadcast(new \App\Events\ChurchDataUpdated($churchId, 'service-planning', 'updated'))->toOthers();

        return $this->successResponse($request, 'Пісню видалено.', 'songs.index');
    }

    // Add song to event setlist
    public function addToEvent(Request $request, Song $song)
    {
        $this->authorizeChurch($song);
        abort_unless(auth()->user()->canEdit('events'), 403);

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'key' => 'nullable|string|max:10',
        ]);

        $event = Event::where('church_id', $this->getCurrentChurch()->id)->findOrFail($validated['event_id']);

        // Get max order
        $maxOrder = $event->songs()->max('order') ?? 0;

        $event->songs()->attach($song->id, [
            'order' => $maxOrder + 1,
            'key' => $validated['key'] ?? $song->key,
        ]);

        $song->incrementUsage();

        return $this->successResponse($request, 'Пісню додано до події.', data: ['order' => $maxOrder + 1]);
    }

    // Event setlist management
    public function eventSetlist(Event $event)
    {
        $this->authorizeChurch($event);

        $songs = $event->songs()->orderBy('order')->get();
        $church = $this->getCurrentChurch();
        $availableSongs = Song::where('church_id', $church->id)
            ->whereNotIn('id', $songs->pluck('id'))
            ->orderBy('title')
            ->get();

        return view('songs.setlist', compact('event', 'songs', 'availableSongs'));
    }

    public function updateSetlistOrder(Request $request, Event $event)
    {
        $this->authorizeChurch($event);
        abort_unless(auth()->user()->canEdit('events'), 403);

        $validated = $request->validate([
            'songs' => 'required|array',
            'songs.*' => [Rule::exists('songs', 'id')->where('church_id', $event->church_id)],
        ]);

        foreach ($validated['songs'] as $order => $songId) {
            $event->songs()->updateExistingPivot($songId, ['order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function removeFromEvent(Request $request, Event $event, Song $song)
    {
        $this->authorizeChurch($event);
        abort_unless(auth()->user()->canEdit('events'), 403);

        $event->songs()->detach($song->id);

        return $this->successResponse($request, 'Пісню видалено з події.');
    }

    public function importPage()
    {
        abort_unless(auth()->user()->canCreate('ministries'), 403);

        return view('songs.import');
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        // Remove UTF-8 BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Detect delimiter
        $firstLine = strtok($content, "\n");
        $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) ? "\t" : ',';

        $csv = Reader::createFromString($content);
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $records = iterator_to_array($csv->getRecords());
        $preview = array_slice($records, 0, 10);

        $autoMappings = $this->detectSongMappings($headers);

        return response()->json([
            'success' => true,
            'headers' => $headers,
            'preview' => array_values($preview),
            'totalRows' => count($records),
            'autoMappings' => $autoMappings,
        ]);
    }

    public function importProcess(Request $request)
    {
        abort_unless(auth()->user()->canCreate('ministries'), 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'mappings' => 'required|array',
        ]);

        $church = $this->getCurrentChurch();
        $mappings = $request->input('mappings');

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $firstLine = strtok($content, "\n");
        $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) ? "\t" : ',';

        $csv = Reader::createFromString($content);
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        $records = iterator_to_array($csv->getRecords());

        DB::beginTransaction();
        try {
            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($records as $index => $row) {
                try {
                    $title = $this->getSongValue($row, $mappings, 'title');
                    if (empty($title)) {
                        $skipped++;

                        continue;
                    }

                    $link = $this->getSongValue($row, $mappings, 'link');
                    $youtubeUrl = null;
                    $spotifyUrl = null;
                    $extraNotes = null;

                    if ($link) {
                        if (preg_match('/youtube\.com|youtu\.be/i', $link)) {
                            $youtubeUrl = $link;
                        } elseif (preg_match('/spotify\.com/i', $link)) {
                            $spotifyUrl = $link;
                        } else {
                            $extraNotes = $link;
                        }
                    }

                    // Validate key
                    $key = $this->getSongValue($row, $mappings, 'key');
                    if ($key && ! isset(Song::keyLabels()[$key])) {
                        // Try extracting from link fragment
                        $key = null;
                    }
                    if (! $key && $link) {
                        $key = $this->extractKeyFromUrl($link);
                    }

                    // Validate BPM
                    $bpm = $this->getSongValue($row, $mappings, 'bpm');
                    if ($bpm) {
                        $bpm = (int) $bpm;
                        if ($bpm < 30 || $bpm > 300) {
                            $bpm = null;
                        }
                    }

                    // Parse tags
                    $tagsRaw = $this->getSongValue($row, $mappings, 'tags');
                    $tags = null;
                    if ($tagsRaw) {
                        $tags = array_values(array_unique(array_filter(
                            array_map('trim', preg_split('/[,;|]/', $tagsRaw))
                        )));
                        if (empty($tags)) {
                            $tags = null;
                        }
                    }

                    // Notes
                    $notes = $this->getSongValue($row, $mappings, 'notes');
                    if ($extraNotes) {
                        $notes = $notes ? $notes."\n".$extraNotes : $extraNotes;
                    }

                    Song::updateOrCreate(
                        [
                            'church_id' => $church->id,
                            'title' => $title,
                        ],
                        [
                            'artist' => $this->getSongValue($row, $mappings, 'artist'),
                            'key' => $key,
                            'bpm' => $bpm,
                            'youtube_url' => $youtubeUrl,
                            'spotify_url' => $spotifyUrl,
                            'tags' => $tags,
                            'lyrics' => $this->getSongValue($row, $mappings, 'lyrics'),
                            'chords' => $this->getSongValue($row, $mappings, 'chords'),
                            'notes' => $notes,
                            'created_by' => auth()->id(),
                        ]
                    );
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = 'Рядок '.($index + 2).': '.$e->getMessage();
                    if (count($errors) > 10) {
                        $errors[] = '... та інші помилки';
                        break;
                    }
                }
            }

            DB::commit();

            broadcast(new \App\Events\ChurchDataUpdated($church->id, 'service-planning', 'updated'))->toOthers();

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function detectSongMappings(array $headers): array
    {
        $mappings = [];
        $fields = [
            'title' => ['title', 'name', 'song', 'назва', 'пісня', 'песня', 'наименование'],
            'artist' => ['artist', 'author', 'автор', 'виконавець', 'исполнитель'],
            'key' => ['key', 'тональність', 'тональность', 'tonality'],
            'bpm' => ['bpm', 'tempo', 'темп'],
            'link' => ['link', 'url', 'посилання', 'ссылка', 'лінк'],
            'tags' => ['tags', 'tag', 'category', 'теги', 'тег', 'категорія', 'статус', 'категория'],
            'lyrics' => ['lyrics', 'text', 'words', 'текст', 'слова'],
            'chords' => ['chords', 'chord', 'акорди', 'аккорды'],
            'notes' => ['notes', 'comment', 'нотатки', 'примітки', 'заметки'],
        ];

        foreach ($headers as $header) {
            $headerLower = mb_strtolower(trim($header));
            foreach ($fields as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if (str_contains($headerLower, $pattern)) {
                        if (! isset($mappings[$field])) {
                            $mappings[$field] = $header;
                        }
                        break 2;
                    }
                }
            }
        }

        return $mappings;
    }

    protected function getSongValue(array $row, array $mappings, string $field): ?string
    {
        if (empty($mappings[$field])) {
            return null;
        }
        $value = $row[$mappings[$field]] ?? null;

        return $value ? trim($value) : null;
    }

    protected function extractKeyFromUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        if (preg_match('/#([A-G][#b]?m?)/', $url, $m)) {
            return isset(Song::keyLabels()[$m[1]]) ? $m[1] : null;
        }

        return null;
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="songs_template.csv"',
        ];

        $columns = ['title', 'artist', 'key', 'bpm', 'link', 'tags', 'lyrics', 'chords', 'notes'];
        $example = ['Amazing Grace', 'John Newton', 'G', '72', 'https://youtube.com/...', 'worship,hymn', '', '', ''];

        $callback = function () use ($columns, $example) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
