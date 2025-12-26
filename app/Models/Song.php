<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Song extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'church_id',
        'title',
        'artist',
        'key',
        'bpm',
        'lyrics',
        'chords',
        'ccli_number',
        'youtube_url',
        'spotify_url',
        'tags',
        'times_used',
        'last_used_at',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'last_used_at' => 'date',
    ];

    const KEYS = [
        'C' => 'До мажор',
        'Cm' => 'До мінор',
        'D' => 'Ре мажор',
        'Dm' => 'Ре мінор',
        'E' => 'Мі мажор',
        'Em' => 'Мі мінор',
        'F' => 'Фа мажор',
        'Fm' => 'Фа мінор',
        'G' => 'Соль мажор',
        'Gm' => 'Соль мінор',
        'A' => 'Ля мажор',
        'Am' => 'Ля мінор',
        'B' => 'Сі мажор',
        'Bm' => 'Сі мінор',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_songs')
            ->withPivot(['order', 'key', 'notes'])
            ->withTimestamps();
    }

    public function getKeyLabelAttribute(): ?string
    {
        return $this->key ? (self::KEYS[$this->key] ?? $this->key) : null;
    }

    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->youtube_url) return null;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->youtube_url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function incrementUsage(): void
    {
        $this->increment('times_used');
        $this->update(['last_used_at' => now()]);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('artist', 'like', "%{$search}%")
                ->orWhere('lyrics', 'like', "%{$search}%");
        });
    }

    public function scopeWithKey($query, ?string $key)
    {
        if (!$key) return $query;
        return $query->where('key', $key);
    }

    public function scopeWithTag($query, ?string $tag)
    {
        if (!$tag) return $query;
        return $query->whereJsonContains('tags', $tag);
    }

    // Parse ChordPro format and return HTML with chords highlighted
    public function getChordsHtmlAttribute(): string
    {
        if (!$this->chords) return '';

        // Convert [Chord] to <span class="chord">Chord</span>
        $html = preg_replace(
            '/\[([A-G][#b]?m?(?:add|sus|dim|aug|maj|min)?[0-9]?(?:\/[A-G][#b]?)?)\]/',
            '<span class="inline-block px-1 py-0.5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-xs font-mono rounded mx-0.5">$1</span>',
            e($this->chords)
        );

        // Convert newlines to <br>
        return nl2br($html);
    }

    // Transpose chords to a different key
    public function transposeChords(string $fromKey, string $toKey): string
    {
        if (!$this->chords || $fromKey === $toKey) {
            return $this->chords ?? '';
        }

        $notes = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
        $fromIndex = array_search(str_replace('m', '', $fromKey), $notes);
        $toIndex = array_search(str_replace('m', '', $toKey), $notes);

        if ($fromIndex === false || $toIndex === false) {
            return $this->chords;
        }

        $semitones = $toIndex - $fromIndex;

        return preg_replace_callback(
            '/\[([A-G][#b]?)([^]]*)\]/',
            function ($matches) use ($notes, $semitones) {
                $note = $matches[1];
                $suffix = $matches[2];

                // Normalize flat to sharp
                $note = str_replace('b', '#', $note);
                if ($note === 'C#') $note = 'Db';

                $noteIndex = array_search($note, $notes);
                if ($noteIndex === false) {
                    // Try without #
                    $noteIndex = array_search(str_replace('#', '', $note), $notes);
                }

                if ($noteIndex === false) {
                    return $matches[0];
                }

                $newIndex = ($noteIndex + $semitones + 12) % 12;
                return '[' . $notes[$newIndex] . $suffix . ']';
            },
            $this->chords
        );
    }
}
