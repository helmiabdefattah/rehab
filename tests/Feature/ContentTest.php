<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Source;
use App\Models\Tag;
use App\Support\VideoCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_database_has_30_to_40_complete_exercises(): void
    {
        $exercises = Exercise::with('tags')->get();
        $this->assertGreaterThanOrEqual(30, $exercises->count());
        $this->assertLessThanOrEqual(40, $exercises->count());

        foreach ($exercises as $e) {
            foreach (['name', 'name_ar', 'category', 'purpose', 'why', 'difficulty', 'duration_label', 'reps_label', 'progression', 'regression'] as $field) {
                $this->assertNotEmpty($e->{$field}, "{$e->slug}.{$field}");
            }
            foreach (['target_muscles', 'target_joints', 'instructions', 'mistakes', 'safety', 'activities'] as $field) {
                $this->assertNotEmpty($e->{$field}, "{$e->slug}.{$field}");
            }
            $this->assertMatchesRegularExpression('/\p{Arabic}/u', $e->name_ar, "{$e->slug} Arabic name");
            $this->assertNotEmpty($e->tags, "{$e->slug} tags");
        }
    }

    public function test_required_tags_exist(): void
    {
        $tags = Tag::pluck('name')->all();

        foreach (['Knee-Friendly', 'Glute Activation', 'Hip Mobility', 'Piriformis-Friendly', 'Low Impact', 'Running', 'Football', 'Strength Training', 'Balance', 'Core'] as $tag) {
            $this->assertContains($tag, $tags);
        }
    }

    public function test_every_exercise_has_a_video_or_an_honest_search_fallback(): void
    {
        foreach (Exercise::all() as $e) {
            if ($e->video_url) {
                $this->assertNotNull($e->videoId(), "{$e->slug} has a parsable YouTube URL");
                $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/', $e->embedUrl());
                $this->assertContains($e->video_verification, ['confirmed', 'single']);
            } else {
                $this->assertStringStartsWith('https://www.youtube.com/results?search_query=', $e->videoSearchUrl());
            }
        }

        foreach ((new VideoCatalog)->all() as $slug => $video) {
            $this->assertNotNull(Exercise::where('slug', $slug)->first(), "videos.php slug {$slug} exists");
            $this->assertMatchesRegularExpression('~^https://www\.youtube\.com/watch\?v=[A-Za-z0-9_-]{11}$~', $video['url']);
        }
    }

    public function test_exercise_text_avoids_unsupported_prevention_or_treatment_claims(): void
    {
        foreach (Exercise::all() as $e) {
            $text = mb_strtolower(implode(' ', [$e->purpose, $e->why, $e->progression, $e->regression, ...$e->instructions, ...$e->safety]));

            foreach (['prevents', 'prevent injur', 'cures', 'treats', 'guarantee', 'injury-proof'] as $claim) {
                $this->assertStringNotContainsString($claim, $text, "{$e->slug}: '{$claim}'");
            }
        }
    }

    public function test_piriformis_work_is_never_aggressive(): void
    {
        $figure4 = Exercise::where('slug', 'figure-4-rocks')->first();

        $this->assertStringContainsString('not a forced stretch', $figure4->purpose);
        $this->assertContains('hip', $figure4->caution);
    }

    public function test_sources_cover_all_topics(): void
    {
        foreach (array_keys(Source::TOPICS) as $topic) {
            $this->assertTrue(Source::where('topic', $topic)->exists(), $topic);
        }
    }
}
