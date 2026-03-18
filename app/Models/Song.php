<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Auditable;

class Song extends Model
{
    use HasFactory, SoftDeletes, Auditable;

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
        'notes',
        'resource_links',
        'times_used',
        'last_used_at',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'resource_links' => 'array',
        'last_used_at' => 'date',
    ];

    /**
     * Get key labels (localized)
     */
    public static function keyLabels(): array
    {
        return [
            // C
            'C' => __('app.key_c_major'),
            'Cm' => __('app.key_c_minor'),
            'C#' => __('app.key_c_sharp_major'),
            'C#m' => __('app.key_c_sharp_minor'),
            // D
            'Db' => __('app.key_d_flat_major'),
            'Dbm' => __('app.key_d_flat_minor'),
            'D' => __('app.key_d_major'),
            'Dm' => __('app.key_d_minor'),
            'D#' => __('app.key_d_sharp_major'),
            'D#m' => __('app.key_d_sharp_minor'),
            // E
            'Eb' => __('app.key_e_flat_major'),
            'Ebm' => __('app.key_e_flat_minor'),
            'E' => __('app.key_e_major'),
            'Em' => __('app.key_e_minor'),
            // F
            'F' => __('app.key_f_major'),
            'Fm' => __('app.key_f_minor'),
            'F#' => __('app.key_f_sharp_major'),
            'F#m' => __('app.key_f_sharp_minor'),
            // G
            'Gb' => __('app.key_g_flat_major'),
            'Gbm' => __('app.key_g_flat_minor'),
            'G' => __('app.key_g_major'),
            'Gm' => __('app.key_g_minor'),
            'G#' => __('app.key_g_sharp_major'),
            'G#m' => __('app.key_g_sharp_minor'),
            // A
            'Ab' => __('app.key_a_flat_major'),
            'Abm' => __('app.key_a_flat_minor'),
            'A' => __('app.key_a_major'),
            'Am' => __('app.key_a_minor'),
            'A#' => __('app.key_a_sharp_major'),
            'A#m' => __('app.key_a_sharp_minor'),
            // B
            'Bb' => __('app.key_b_flat_major'),
            'Bbm' => __('app.key_b_flat_minor'),
            'B' => __('app.key_b_major'),
            'Bm' => __('app.key_b_minor'),
        ];
    }

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
        return $this->key ? (self::keyLabels()[$this->key] ?? $this->key) : null;
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

        $search = addcslashes($search, '%_');
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
        $flatToSharpKey = [
            'Cb' => 'B', 'Db' => 'C#', 'Eb' => 'D#', 'Fb' => 'E',
            'Gb' => 'F#', 'Ab' => 'G#', 'Bb' => 'A#',
        ];
        $fromNote = str_replace('m', '', $fromKey);
        $toNote = str_replace('m', '', $toKey);
        $fromNote = $flatToSharpKey[$fromNote] ?? $fromNote;
        $toNote = $flatToSharpKey[$toNote] ?? $toNote;
        $fromIndex = array_search($fromNote, $notes);
        $toIndex = array_search($toNote, $notes);

        if ($fromIndex === false || $toIndex === false) {
            return $this->chords;
        }

        $semitones = $toIndex - $fromIndex;

        return preg_replace_callback(
            '/\[([A-G][#b]?)([^]]*)\]/',
            function ($matches) use ($notes, $semitones) {
                $note = $matches[1];
                $suffix = $matches[2];

                // Normalize flat to sharp (enharmonic equivalents)
                $flatToSharp = [
                    'Cb' => 'B', 'Db' => 'C#', 'Eb' => 'D#', 'Fb' => 'E',
                    'Gb' => 'F#', 'Ab' => 'G#', 'Bb' => 'A#',
                ];
                $note = $flatToSharp[$note] ?? $note;

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
