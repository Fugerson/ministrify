<?php

namespace App\Imports;

use App\Models\Song;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SongsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $churchId;
    protected int $userId;

    public function __construct(int $churchId, int $userId)
    {
        $this->churchId = $churchId;
        $this->userId = $userId;
    }

    public function model(array $row)
    {
        $title = trim($row['title'] ?? $row['nazva'] ?? '');

        if (empty($title)) {
            return null;
        }

        $key = trim($row['key'] ?? $row['tonalnist'] ?? '');
        if ($key && !array_key_exists($key, Song::KEYS)) {
            $key = null;
        }

        $bpm = $row['bpm'] ?? null;
        if ($bpm !== null && $bpm !== '') {
            $bpm = (int) $bpm;
            if ($bpm < 30 || $bpm > 300) {
                $bpm = null;
            }
        } else {
            $bpm = null;
        }

        $tagsRaw = $row['tags'] ?? $row['tehy'] ?? null;
        $tags = null;
        if (!empty($tagsRaw)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
            if (empty($tags)) {
                $tags = null;
            }
        }

        return Song::updateOrCreate(
            [
                'church_id' => $this->churchId,
                'title' => $title,
            ],
            [
                'artist' => $this->getField($row, 'artist', 'avtor'),
                'key' => $key ?: null,
                'bpm' => $bpm,
                'lyrics' => $this->getField($row, 'lyrics', 'tekst'),
                'chords' => $this->getField($row, 'chords', 'akordy'),
                'ccli_number' => $this->getField($row, 'ccli_number', 'ccli'),
                'youtube_url' => $this->getField($row, 'youtube_url', 'youtube'),
                'spotify_url' => $this->getField($row, 'spotify_url', 'spotify'),
                'tags' => $tags,
                'notes' => $this->getField($row, 'notes', 'notatky'),
                'created_by' => $this->userId,
            ]
        );
    }

    protected function getField(array $row, string $english, string $ukrainian): ?string
    {
        $value = $row[$english] ?? $row[$ukrainian] ?? null;
        return !empty($value) ? trim((string) $value) : null;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'nazva' => 'nullable|string|max:255',
        ];
    }
}
