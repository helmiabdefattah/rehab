<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_database_has_complete_warm_up_and_workout_exercises(): void
    {
        $exercises = Exercise::with('tags')->get();
        $this->assertGreaterThanOrEqual(60, $exercises->count());
        $this->assertGreaterThanOrEqual(24, $exercises->where('section', 'workout')->count());
        $this->assertGreaterThanOrEqual(30, $exercises->where('section', 'warmup')->count());

        foreach ($exercises as $e) {
            foreach (['name', 'name_ar', 'category', 'purpose', 'why', 'difficulty', 'duration_label', 'reps_label', 'progression', 'regression', 'animation'] as $field) {
                $this->assertNotEmpty($e->{$field}, "{$e->slug}.{$field}");
            }
            foreach (['target_muscles', 'instructions', 'mistakes', 'safety', 'activities'] as $field) {
                $this->assertNotEmpty($e->{$field}, "{$e->slug}.{$field}");
            }
            $this->assertContains($e->section, ['warmup', 'workout'], "{$e->slug} section");
            $this->assertMatchesRegularExpression('/\p{Arabic}/u', $e->name_ar, "{$e->slug} Arabic name");
            $this->assertNotEmpty($e->tags, "{$e->slug} tags");
        }
    }

    public function test_every_exercise_belongs_to_at_least_one_valid_split(): void
    {
        $splits = ['push', 'pull', 'legs', 'cardio-core'];

        foreach (Exercise::all() as $e) {
            $this->assertNotEmpty($e->activities, "{$e->slug} has splits");
            $this->assertEmpty(array_diff($e->activities, $splits), "{$e->slug} valid splits");
        }
    }

    public function test_each_split_has_a_full_workout(): void
    {
        foreach (['push', 'pull', 'legs', 'cardio-core'] as $split) {
            $slugs = config("warmup.workouts.{$split}");
            $this->assertCount(6, $slugs, "{$split} workout size");

            foreach ($slugs as $slug) {
                $exercise = Exercise::where('slug', $slug)->first();
                $this->assertNotNull($exercise, "{$split}: {$slug} exists");
                $this->assertSame('workout', $exercise->section, "{$slug} is a workout exercise");
                $this->assertNotEmpty($exercise->sets, "{$slug} has a set prescription");
            }
        }
    }

    public function test_required_tags_exist(): void
    {
        $tags = Tag::pluck('name')->all();

        foreach (['Knee-Friendly', 'Glute Activation', 'Hip Mobility', 'Shoulder-Friendly', 'Compound', 'Core', 'Balance', 'Low Impact'] as $tag) {
            $this->assertContains($tag, $tags);
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

    public function test_sources_cover_all_topics(): void
    {
        foreach (array_keys(Source::TOPICS) as $topic) {
            $this->assertTrue(Source::where('topic', $topic)->exists(), $topic);
        }
    }
}
