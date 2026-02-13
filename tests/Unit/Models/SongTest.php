<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    private function createSong(array $attrs = []): Song
    {
        return Song::create(array_merge([
            'church_id' => $this->church->id,
            'title' => 'Великий Бог',
            'artist' => 'Hillsong',
            'times_used' => 0,
        ], $attrs));
    }

    // ==================
    // Key Label
    // ==================

    public function test_key_label_for_c_major(): void
    {
        $song = $this->createSong(['key' => 'C']);
        $this->assertEquals('До мажор', $song->key_label);
    }

    public function test_key_label_for_a_minor(): void
    {
        $song = $this->createSong(['key' => 'Am']);
        $this->assertEquals('Ля мінор', $song->key_label);
    }

    public function test_key_label_null_when_no_key(): void
    {
        $song = $this->createSong(['key' => null]);
        $this->assertNull($song->key_label);
    }

    public function test_key_label_returns_raw_for_unknown(): void
    {
        $song = $this->createSong(['key' => 'Xm']);
        $this->assertEquals('Xm', $song->key_label);
    }

    // ==================
    // YouTube ID
    // ==================

    public function test_youtube_id_from_full_url(): void
    {
        $song = $this->createSong([
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->assertEquals('dQw4w9WgXcQ', $song->youtube_id);
    }

    public function test_youtube_id_from_short_url(): void
    {
        $song = $this->createSong([
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);

        $this->assertEquals('dQw4w9WgXcQ', $song->youtube_id);
    }

    public function test_youtube_id_null_when_no_url(): void
    {
        $song = $this->createSong(['youtube_url' => null]);
        $this->assertNull($song->youtube_id);
    }

    public function test_youtube_id_null_for_invalid_url(): void
    {
        $song = $this->createSong([
            'youtube_url' => 'https://example.com/not-youtube',
        ]);

        $this->assertNull($song->youtube_id);
    }

    // ==================
    // Increment Usage
    // ==================

    public function test_increment_usage(): void
    {
        $song = $this->createSong(['times_used' => 5]);

        $song->incrementUsage();
        $song->refresh();

        $this->assertEquals(6, $song->times_used);
        $this->assertNotNull($song->last_used_at);
    }

    // ==================
    // Scopes
    // ==================

    public function test_search_scope_by_title(): void
    {
        $this->createSong(['title' => 'Великий Бог']);
        $this->createSong(['title' => 'Інша пісня']);

        $results = Song::search('Великий')->get();
        $this->assertCount(1, $results);
    }

    public function test_search_scope_by_artist(): void
    {
        $this->createSong(['artist' => 'Hillsong']);
        $this->createSong(['artist' => 'Bethel']);

        $results = Song::search('Hillsong')->get();
        $this->assertCount(1, $results);
    }

    public function test_search_scope_null_returns_all(): void
    {
        $this->createSong();
        $this->createSong(['title' => 'Song 2']);

        $results = Song::search(null)->get();
        $this->assertCount(2, $results);
    }

    public function test_with_key_scope(): void
    {
        $this->createSong(['key' => 'C']);
        $this->createSong(['key' => 'G']);

        $results = Song::withKey('C')->get();
        $this->assertCount(1, $results);
    }

    public function test_with_key_scope_null_returns_all(): void
    {
        $this->createSong(['key' => 'C']);
        $this->createSong(['key' => 'G']);

        $results = Song::withKey(null)->get();
        $this->assertCount(2, $results);
    }
}
