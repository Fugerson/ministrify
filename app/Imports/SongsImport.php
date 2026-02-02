<?php

namespace App\Imports;

use App\Models\Song;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class SongsImport implements ToModel, WithHeadingRow, WithValidation, WithEvents
{
    protected int $churchId;
    protected int $userId;

    protected const TITLE_KEYS = ['title', 'nazva', 'назва', 'name', 'пісня', 'song', 'найменування'];
    protected const ARTIST_KEYS = ['artist', 'avtor', 'автор', 'виконавець', 'author'];
    protected const KEY_KEYS = ['key', 'tonalnist', 'тональність', 'тон'];
    protected const BPM_KEYS = ['bpm', 'темп', 'tempo'];
    protected const LYRICS_KEYS = ['lyrics', 'tekst', 'текст', 'слова'];
    protected const CHORDS_KEYS = ['chords', 'akordy', 'акорди'];
    protected const CCLI_KEYS = ['ccli_number', 'ccli'];
    protected const YOUTUBE_KEYS = ['youtube_url', 'youtube'];
    protected const SPOTIFY_KEYS = ['spotify_url', 'spotify'];
    protected const TAGS_KEYS = ['tags', 'tehy', 'теги', 'статус', 'status', 'категорія', 'category'];
    protected const NOTES_KEYS = ['notes', 'notatky', 'нотатки', 'примітки'];
    protected const LINK_KEYS = ['link', 'url', 'посилання', 'лінк', 'href'];

    public function __construct(int $churchId, int $userId)
    {
        $this->churchId = $churchId;
        $this->userId = $userId;

        // Preserve original headers (including Cyrillic) instead of slugifying them
        HeadingRowFormatter::default('none');
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                HeadingRowFormatter::default('slug');
            },
        ];
    }

    public function model(array $row)
    {
        $row = $this->normalizeKeys($row);

        $title = trim((string) $this->resolve($row, self::TITLE_KEYS));
        if (empty($title)) {
            return null;
        }

        // Key: from explicit column or extracted from URL fragment
        $key = trim((string) $this->resolve($row, self::KEY_KEYS));
        $link = trim((string) $this->resolve($row, self::LINK_KEYS));

        if (empty($key) && !empty($link)) {
            $key = $this->extractKeyFromUrl($link);
        }
        if ($key && !array_key_exists($key, Song::KEYS)) {
            $key = null;
        }

        // BPM
        $bpm = $this->resolve($row, self::BPM_KEYS);
        if ($bpm !== null && $bpm !== '') {
            $bpm = (int) $bpm;
            if ($bpm < 30 || $bpm > 300) {
                $bpm = null;
            }
        } else {
            $bpm = null;
        }

        // Tags
        $tagsRaw = $this->resolve($row, self::TAGS_KEYS);
        $tags = null;
        if (!empty($tagsRaw)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
            if (empty($tags)) {
                $tags = null;
            }
        }

        // URLs: from explicit columns or auto-detected from link column
        $youtubeUrl = $this->resolve($row, self::YOUTUBE_KEYS);
        $spotifyUrl = $this->resolve($row, self::SPOTIFY_KEYS);
        $notes = $this->resolve($row, self::NOTES_KEYS);

        if (!empty($link)) {
            if (empty($youtubeUrl) && preg_match('/youtube\.com|youtu\.be/i', $link)) {
                $youtubeUrl = $link;
            } elseif (empty($spotifyUrl) && preg_match('/spotify\.com/i', $link)) {
                $spotifyUrl = $link;
            } elseif (empty($youtubeUrl) && empty($spotifyUrl)) {
                // Other link (holychords, kg-music, etc.) → append to notes
                $cleanLink = preg_replace('/#.*$/', '', $link);
                $notes = $notes ? $notes . "\n" . $cleanLink : $cleanLink;
            }
        }

        return Song::updateOrCreate(
            [
                'church_id' => $this->churchId,
                'title' => $title,
            ],
            [
                'artist' => $this->resolveString($row, self::ARTIST_KEYS),
                'key' => $key ?: null,
                'bpm' => $bpm,
                'lyrics' => $this->resolveString($row, self::LYRICS_KEYS),
                'chords' => $this->resolveString($row, self::CHORDS_KEYS),
                'ccli_number' => $this->resolveString($row, self::CCLI_KEYS),
                'youtube_url' => !empty($youtubeUrl) ? trim($youtubeUrl) : null,
                'spotify_url' => !empty($spotifyUrl) ? trim($spotifyUrl) : null,
                'tags' => $tags,
                'notes' => !empty($notes) ? trim($notes) : null,
                'created_by' => $this->userId,
            ]
        );
    }

    protected function normalizeKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[mb_strtolower(trim((string) $key))] = $value;
        }
        return $normalized;
    }

    protected function resolve(array $row, array $aliases)
    {
        foreach ($aliases as $key) {
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                return $row[$key];
            }
        }
        return null;
    }

    protected function resolveString(array $row, array $aliases): ?string
    {
        $value = $this->resolve($row, $aliases);
        return !empty($value) ? trim((string) $value) : null;
    }

    protected function extractKeyFromUrl(string $url): ?string
    {
        if (preg_match('/#([A-G][#b]?m?)$/i', $url, $matches)) {
            $candidate = $matches[1];
            // Normalize first letter to uppercase
            $candidate = strtoupper($candidate[0]) . substr($candidate, 1);
            if (array_key_exists($candidate, Song::KEYS)) {
                return $candidate;
            }
        }
        return null;
    }

    public function rules(): array
    {
        return [];
    }
}
