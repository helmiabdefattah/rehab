<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Support\VideoCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VideoCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_verify_reports_resolving_and_broken_links(): void
    {
        Http::fake(function ($request) {
            return str_contains($request->url(), 'WtilA9IJX1c')
                ? Http::response(['title' => 'Glute Bridges Exercise for Hips & Butt', 'author_name' => 'Release Physical Therapy'])
                : Http::response('Not Found', 404);
        });

        $this->artisan('videos:verify')
            ->expectsOutputToContain('FAILED (HTTP 404)')
            ->assertFailed();
    }

    public function test_verify_succeeds_when_all_links_resolve(): void
    {
        Http::fake(['www.youtube.com/*' => Http::response(['title' => 'Demo', 'author_name' => 'Channel'])]);

        $this->artisan('videos:verify')->assertSuccessful();
    }

    public function test_set_video_updates_catalog_and_database(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'videos').'.php';
        copy(database_path('data/videos.php'), $path);
        $this->app->instance(VideoCatalog::class, new VideoCatalog($path));

        $this->artisan('videos:set', [
            'slug' => 'calf-raises',
            'url' => 'https://www.youtube.com/watch?v=abcdefghijk',
            '--title' => 'Calf raises',
            '--channel' => 'Test Physio',
            '--source' => 'physiotherapy',
        ])->assertSuccessful();

        $catalog = (new VideoCatalog($path))->all();
        $this->assertSame('https://www.youtube.com/watch?v=abcdefghijk', $catalog['calf-raises']['url']);
        $this->assertArrayHasKey('glute-bridge', $catalog);
        $this->assertStringContainsString('Curated instructional videos', file_get_contents($path));
        $this->assertSame('abcdefghijk', Exercise::where('slug', 'calf-raises')->first()->videoId());

        $this->artisan('videos:set', ['slug' => 'calf-raises', 'url' => 'https://example.com/video'])->assertFailed();

        unlink($path);
    }
}
