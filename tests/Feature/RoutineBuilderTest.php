<?php

namespace Tests\Feature;

use App\Enums\Activity;
use App\Enums\Intensity;
use App\Models\Exercise;
use App\Services\Warmup\ReadinessInput;
use App\Services\Warmup\RoutineBuilder;
use App\Services\Warmup\RoutineRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private const ALL_EQUIPMENT = ['mini-band', 'long-band', 'step', 'bike', 'ball'];

    private function build(string $program, int $minutes, string $intensity = 'moderate', int $level = 1, array $readiness = ['checked' => true, 'score' => 8], array $equipment = self::ALL_EQUIPMENT): array
    {
        return app(RoutineBuilder::class)->build(new RoutineRequest(
            program: $program,
            activity: Activity::tryFrom($program) ?? Activity::General,
            minutes: $minutes,
            intensity: Intensity::from($intensity),
            level: $level,
            equipment: $equipment,
            readiness: ReadinessInput::fromArray($readiness),
        ));
    }

    public function test_every_combination_fits_the_requested_time_and_is_well_formed(): void
    {
        $stageOrder = ['heat' => 1, 'mobility' => 2, 'activation' => 3, 'dynamic' => 4];

        foreach (['gym', 'running', 'football', 'general'] as $activity) {
            foreach ([5, 10, 15, 20] as $minutes) {
                foreach (['light', 'moderate', 'high'] as $intensity) {
                    foreach ([1, 2, 3] as $level) {
                        foreach ([['mini-band', 'step', 'ball'], []] as $equipment) {
                            $r = $this->build($activity, $minutes, $intensity, $level, equipment: $equipment);
                            $label = "{$activity}/{$minutes}/{$intensity}/L{$level}/".count($equipment);

                            $this->assertSame($minutes * 60, $r['total_seconds'], "total time {$label}");
                            $this->assertGreaterThanOrEqual(4, count($r['items']), "item count {$label}");

                            $slugs = array_column($r['items'], 'slug');
                            $this->assertSame($slugs, array_values(array_unique($slugs)), "no duplicates {$label}");

                            $stages = array_map(fn ($i) => $stageOrder[$i['stage']], $r['items']);
                            $sorted = $stages;
                            sort($sorted);
                            $this->assertSame($sorted, $stages, "stages progress in order {$label}");
                            $this->assertSame('heat', $r['items'][0]['stage'], "starts with general heat {$label}");

                            foreach ($r['items'] as $item) {
                                $ex = Exercise::where('slug', $item['slug'])->first();
                                $this->assertLessThanOrEqual($level, $ex->min_level, "{$item['slug']} level {$label}");
                                $this->assertEmpty(array_diff($ex->equipment, $equipment), "{$item['slug']} equipment {$label}");
                                $this->assertGreaterThanOrEqual(15, $item['seconds'], "{$item['slug']} min duration {$label}");
                                if ($item['per_side']) {
                                    $this->assertSame(0, $item['seconds'] % 10, "{$item['slug']} splits evenly per side");
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function test_caution_readiness_keeps_everything_low_impact_and_removes_symptomatic_exercises(): void
    {
        foreach (['running', 'football', 'gym', 'general'] as $activity) {
            $r = $this->build($activity, 15, 'high', 3, ['checked' => true, 'knee_swelling' => true, 'piriformis_pain' => true, 'score' => 6]);

            $this->assertSame('caution', $r['readiness']['status']);
            $this->assertSame(1, $r['level']);
            $this->assertSame('light', $r['intensity']);

            foreach ($r['items'] as $item) {
                $ex = Exercise::where('slug', $item['slug'])->first();
                $this->assertSame('Low', $ex->impact->value, "{$activity}: {$item['slug']} must be low impact");
                $this->assertEmpty(array_intersect($ex->caution, ['knee', 'hip']), "{$activity}: {$item['slug']} excluded by symptoms");
            }
        }
    }

    public function test_running_progresses_from_marching_to_skipping_to_jogging_to_build_ups(): void
    {
        $slugs = array_column($this->build('running', 15, 'moderate', 2)['items'], 'slug');

        foreach (['calf-raises', 'a-march', 'a-skip', 'easy-jog', 'build-up-runs'] as $slug) {
            $this->assertContains($slug, $slugs);
        }
        $pos = array_flip($slugs);
        $this->assertTrue($pos['a-march'] < $pos['a-skip'] && $pos['a-skip'] < $pos['easy-jog'] && $pos['easy-jog'] < $pos['build-up-runs']);
    }

    public function test_football_builds_up_to_lateral_movement_acceleration_change_of_direction_and_ball_work(): void
    {
        $slugs = array_column($this->build('football', 15, 'moderate', 2)['items'], 'slug');
        $pos = array_flip($slugs);

        foreach (['lateral-shuffle', 'build-up-runs', 'change-of-direction', 'football-ball-work'] as $slug) {
            $this->assertContains($slug, $slugs);
        }
        $this->assertTrue($pos['lateral-shuffle'] < $pos['build-up-runs'] && $pos['build-up-runs'] < $pos['change-of-direction'] && $pos['change-of-direction'] < $pos['football-ball-work']);
    }

    public function test_short_football_routines_still_finish_with_ball_work(): void
    {
        foreach (['light', 'moderate', 'high'] as $intensity) {
            $slugs = array_column($this->build('football', 5, $intensity, 1)['items'], 'slug');
            $this->assertSame('football-ball-work', end($slugs), $intensity);
        }
    }

    public function test_level_one_never_starts_with_sprinting_cutting_or_skipping(): void
    {
        foreach (['running', 'football'] as $activity) {
            $slugs = array_column($this->build($activity, 20, 'high', 1)['items'], 'slug');

            foreach (['a-skip', 'build-up-runs', 'change-of-direction', 'reverse-lunge', 'single-leg-rdl'] as $slug) {
                $this->assertNotContains($slug, $slugs, "{$activity} level 1 should not include {$slug}");
            }
        }
    }

    public function test_gym_ends_with_pattern_rehearsal_and_warm_up_sets(): void
    {
        $slugs = array_column($this->build('gym', 10, 'moderate', 1)['items'], 'slug');

        $this->assertContains('hip-hinge', $slugs);
        $this->assertSame('ramp-up-sets', end($slugs));
    }

    public function test_missing_equipment_is_substituted_and_explained(): void
    {
        $r = $this->build('gym', 10, 'moderate', 1, equipment: []);
        $slugs = array_column($r['items'], 'slug');

        $this->assertNotContains('easy-cycling', $slugs);
        $this->assertNotContains('banded-glute-bridge', $slugs);
        $this->assertContains('glute-bridge', $slugs);
        $this->assertNotEmpty(array_filter($r['adjustments'], fn ($a) => str_contains($a, 'needs')));
    }

    public function test_focus_programmes_build_knee_and_hip_routines(): void
    {
        $knee = array_column($this->build('knee', 10, level: 1, readiness: ['checked' => false])['items'], 'slug');
        $hip = array_column($this->build('hip', 10, level: 1, readiness: ['checked' => false])['items'], 'slug');

        $this->assertContains('calf-raises', $knee);
        $this->assertContains('ankle-rocks', $knee);
        $this->assertContains('clamshell', $hip);
        $this->assertContains('hip-90-90', $hip);
    }
}
