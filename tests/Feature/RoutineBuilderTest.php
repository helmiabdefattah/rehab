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

    private const ALL_EQUIPMENT = ['mini-band', 'long-band', 'step', 'bike'];

    private function build(string $program, int $minutes, string $intensity = 'moderate', int $level = 2, array $equipment = self::ALL_EQUIPMENT): array
    {
        return app(RoutineBuilder::class)->build(new RoutineRequest(
            program: $program,
            activity: Activity::tryFrom($program) ?? Activity::CardioCore,
            minutes: $minutes,
            intensity: Intensity::from($intensity),
            level: $level,
            equipment: $equipment,
            readiness: ReadinessInput::unchecked(),
        ));
    }

    public function test_every_split_and_combination_fits_the_time_and_is_well_formed(): void
    {
        $stageOrder = ['heat' => 1, 'mobility' => 2, 'activation' => 3, 'dynamic' => 4];

        foreach (['push', 'pull', 'legs', 'cardio-core', 'full-body'] as $program) {
            foreach ([5, 10, 15, 20] as $minutes) {
                foreach (['light', 'moderate', 'high'] as $intensity) {
                    foreach ([1, 2, 3] as $level) {
                        foreach ([['mini-band', 'long-band', 'step'], []] as $equipment) {
                            $r = $this->build($program, $minutes, $intensity, $level, $equipment);
                            $label = "{$program}/{$minutes}/{$intensity}/L{$level}/".count($equipment);

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
                                $this->assertSame('warmup', $ex->section, "{$item['slug']} is a warm-up {$label}");
                                $this->assertLessThanOrEqual($level, $ex->min_level, "{$item['slug']} level {$label}");
                                $this->assertEmpty(array_diff($ex->equipment, $equipment), "{$item['slug']} equipment {$label}");
                                $this->assertNotEmpty($item['exercise']['animation']['pattern'], "{$item['slug']} animation {$label}");
                            }
                        }
                    }
                }
            }
        }
    }

    public function test_push_warm_up_primes_the_shoulders_and_rehearses_pressing(): void
    {
        $slugs = array_column($this->build('push', 15, 'moderate', 2)['items'], 'slug');

        $this->assertContains('band-pull-apart', $slugs);
        $this->assertContains('shoulder-pass-through', $slugs);
        $this->assertSame('ramp-up-sets', end($slugs));
    }

    public function test_pull_warm_up_switches_on_the_back(): void
    {
        $slugs = array_column($this->build('pull', 15, 'moderate', 2)['items'], 'slug');

        $this->assertContains('scapular-pull', $slugs);
        $this->assertContains('band-pull-apart', $slugs);
        $this->assertSame('ramp-up-sets', end($slugs));
    }

    public function test_legs_warm_up_activates_glutes_and_ends_with_pattern_rehearsal(): void
    {
        $slugs = array_column($this->build('legs', 10, 'moderate', 2)['items'], 'slug');

        $this->assertContains('ankle-rocks', $slugs);
        $this->assertContains('hip-hinge', $slugs);
        $this->assertSame('ramp-up-sets', end($slugs));
    }

    public function test_cardio_core_builds_running_mechanics_in_order(): void
    {
        $slugs = array_column($this->build('cardio-core', 15, 'moderate', 2)['items'], 'slug');

        foreach (['a-march', 'a-skip', 'easy-jog', 'build-up-runs'] as $slug) {
            $this->assertContains($slug, $slugs);
        }
        $pos = array_flip($slugs);
        $this->assertTrue($pos['a-march'] < $pos['a-skip'] && $pos['a-skip'] < $pos['easy-jog'] && $pos['easy-jog'] < $pos['build-up-runs']);
    }

    public function test_level_one_never_includes_skipping_or_build_ups(): void
    {
        $slugs = array_column($this->build('cardio-core', 20, 'high', 1)['items'], 'slug');

        foreach (['a-skip', 'build-up-runs'] as $slug) {
            $this->assertNotContains($slug, $slugs, "level 1 should not include {$slug}");
        }
    }

    public function test_missing_equipment_is_substituted_and_explained(): void
    {
        $r = $this->build('legs', 10, 'moderate', 2, equipment: []);
        $slugs = array_column($r['items'], 'slug');

        $this->assertNotContains('easy-cycling', $slugs);
        $this->assertNotContains('banded-glute-bridge', $slugs);
        $this->assertContains('glute-bridge', $slugs);
        $this->assertNotEmpty(array_filter($r['adjustments'], fn ($a) => str_contains($a, 'needs')));
    }
}
