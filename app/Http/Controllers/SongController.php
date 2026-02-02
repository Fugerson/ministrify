<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class SongController extends Controller
{
    public function index(Request $request)
    {
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

        return view('songs.index', compact('songs', 'allTags'));
    }

    public function create()
    {
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|numeric|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|max:255',
            'spotify_url' => 'nullable|max:255',
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
        $validated['youtube_url'] = !empty($validated['youtube_url']) ? $validated['youtube_url'] : null;
        $validated['spotify_url'] = !empty($validated['spotify_url']) ? $validated['spotify_url'] : null;
        $validated['bpm'] = !empty($validated['bpm']) ? (int)$validated['bpm'] : null;

        // Filter out empty links
        $resourceLinks = collect($validated['resource_links'] ?? [])->filter(fn($l) => !empty($l['label']) && !empty($l['url']))->values()->all();

        // Combine selected tags with new tag
        $tags = $validated['tags'] ?? [];
        if (!empty($validated['new_tag'])) {
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
            'tags' => !empty($tags) ? array_values($tags) : null,
            'notes' => $validated['notes'],
            'resource_links' => !empty($resourceLinks) ? $resourceLinks : null,
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
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
                ]
            ]);
        }

        return redirect()->route('songs.index')
            ->with('success', 'Пісню додано до бібліотеки.');
    }

    public function show(Song $song)
    {
        $this->authorizeChurch($song);

        $song->load(['creator', 'events' => fn($q) => $q->latest('date')->limit(10)]);

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

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|numeric|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|max:255',
            'spotify_url' => 'nullable|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'new_tag' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
            'resource_links' => 'nullable|array|max:20',
            'resource_links.*.label' => 'required|string|max:255',
            'resource_links.*.url' => 'required|url|max:500',
        ]);

        // Clean empty values
        $validated['youtube_url'] = !empty($validated['youtube_url']) ? $validated['youtube_url'] : null;
        $validated['spotify_url'] = !empty($validated['spotify_url']) ? $validated['spotify_url'] : null;
        $validated['bpm'] = !empty($validated['bpm']) ? (int)$validated['bpm'] : null;

        // Filter out empty links
        $resourceLinks = collect($validated['resource_links'] ?? [])->filter(fn($l) => !empty($l['label']) && !empty($l['url']))->values()->all();

        // Combine selected tags with new tag
        $tags = $validated['tags'] ?? [];
        if (!empty($validated['new_tag'])) {
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
            'tags' => !empty($tags) ? array_values($tags) : null,
            'notes' => $validated['notes'],
            'resource_links' => !empty($resourceLinks) ? $resourceLinks : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
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
                ]
            ]);
        }

        return redirect()->route('songs.show', $song)
            ->with('success', 'Пісню оновлено.');
    }

    public function destroy(Request $request, Song $song)
    {
        $this->authorizeChurch($song);

        $song->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('songs.index')
            ->with('success', 'Пісню видалено.');
    }

    // Add song to event setlist
    public function addToEvent(Request $request, Song $song)
    {
        $this->authorizeChurch($song);

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'key' => 'nullable|string|max:10',
        ]);

        $event = Event::findOrFail($validated['event_id']);
        $this->authorizeChurch($event);

        // Get max order
        $maxOrder = $event->songs()->max('order') ?? 0;

        $event->songs()->attach($song->id, [
            'order' => $maxOrder + 1,
            'key' => $validated['key'] ?? $song->key,
        ]);

        $song->incrementUsage();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'order' => $maxOrder + 1]);
        }

        return back()->with('success', 'Пісню додано до події.');
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

        $validated = $request->validate([
            'songs' => 'required|array',
            'songs.*' => 'exists:songs,id',
        ]);

        foreach ($validated['songs'] as $order => $songId) {
            $event->songs()->updateExistingPivot($songId, ['order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function removeFromEvent(Request $request, Event $event, Song $song)
    {
        $this->authorizeChurch($event);

        $event->songs()->detach($song->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Пісню видалено з події.');
    }

    public function importPage()
    {
        return view('songs.import');
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        // Remove UTF-8 BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Detect delimiter
        $firstLine = strtok($content, "\n");
        $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";

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
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'mappings' => 'required|array',
        ]);

        $church = $this->getCurrentChurch();
        $mappings = $request->input('mappings');

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $firstLine = strtok($content, "\n");
        $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";

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
                    if ($key && !isset(Song::KEYS[$key])) {
                        // Try extracting from link fragment
                        $key = null;
                    }
                    if (!$key && $link) {
                        $key = $this->extractKeyFromUrl($link);
                    }

                    // Validate BPM
                    $bpm = $this->getSongValue($row, $mappings, 'bpm');
                    if ($bpm) {
                        $bpm = (int) $bpm;
                        if ($bpm < 30 || $bpm > 300) $bpm = null;
                    }

                    // Parse tags
                    $tagsRaw = $this->getSongValue($row, $mappings, 'tags');
                    $tags = null;
                    if ($tagsRaw) {
                        $tags = array_values(array_unique(array_filter(
                            array_map('trim', preg_split('/[,;|]/', $tagsRaw))
                        )));
                        if (empty($tags)) $tags = null;
                    }

                    // Notes
                    $notes = $this->getSongValue($row, $mappings, 'notes');
                    if ($extraNotes) {
                        $notes = $notes ? $notes . "\n" . $extraNotes : $extraNotes;
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
                    $errors[] = "Рядок " . ($index + 2) . ": " . $e->getMessage();
                    if (count($errors) > 10) {
                        $errors[] = "... та інші помилки";
                        break;
                    }
                }
            }

            DB::commit();

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
                        if (!isset($mappings[$field])) {
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
        if (!$url) return null;
        if (preg_match('/#([A-G][#b]?m?)/', $url, $m)) {
            return isset(Song::KEYS[$m[1]]) ? $m[1] : null;
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
