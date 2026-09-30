<?php

namespace App\Services\Warmup;

use App\Enums\Impact;
use App\Enums\Intensity;
use App\Enums\Level;
use App\Enums\ReadinessStatus;
use App\Enums\Stage;
use App\Models\Exercise;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Generates a personalised four-stage warm-up.
 *
 * 1. Readiness answers set caps on level, intensity and impact.
 * 2. Each template slot picks the first *suitable* candidate
 *    (level, intensity, impact, equipment, symptom exclusions, not used yet).
 * 3. Slots are admitted by priority until the stage's time budget is used.
 * 4. Durations are scaled (within per-exercise min/max) so the whole routine,
 *    including transitions, matches the requested minutes.
 */
class RoutineBuilder
{
    private const STEP = 5;

    public function __construct(private readonly ReadinessEvaluator $evaluator) {}

    public function build(RoutineRequest $request): array
    {
        $template = config("warmup.templates.{$request->program}");

        if (! is_array($template)) {
            throw new InvalidArgumentException("Unknown warm-up program [{$request->program}].");
        }

        $readiness = $this->evaluator->evaluate($request->readiness);
        $level = min($request->level, $readiness->maxLevel);
        $intensity = $request->intensity->min($readiness->maxIntensity);
        $transition = max(0, $request->transitionSeconds);

        $ctx = [
            'level' => $level,
            'intensity' => $intensity,
            'maxImpact' => $readiness->maxImpact,
            'equipment' => $request->equipment,
            'exclude' => $readiness->excludeGroups,
        ];

        $exercises = Exercise::with('tags')->get()->keyBy('slug');
        $budgets = $this->budgets($template, $request->minutes, $intensity, $readiness->status);
        $adjustments = $this->capAdjustments($request, $level, $intensity, $readiness->status);
        $used = [];
        $stages = [];

        foreach (Stage::cases() as $stage) {
            $slots = $template[$stage->value] ?? [];
            $chosen = [];

            // Pick exercises slot by slot in priority order so essential slots choose first.
            $order = collect($slots)->map(fn ($slot, $i) => [...$slot, 'i' => $i])->sortBy([['p', 'asc'], ['i', 'asc']]);

            foreach ($order as $slot) {
                $candidates = $slot['levels'][$level] ?? $slot['pick'];
                [$exercise, $note] = $this->pick($candidates, $exercises, $ctx, $used);

                if ($note) {
                    $adjustments[] = $note;
                }

                if (! $exercise) {
                    continue;
                }

                $chosen[] = ['slot' => $slot['i'], 'p' => $slot['p'], 'keep' => $slot['keep'] ?? false, 'exercise' => $exercise];
                $used[$exercise->slug] = true;
            }

            $stages[$stage->value] = $this->fitStage($chosen, $budgets[$stage->value], $transition, $used);
        }

        $items = $this->balance($stages, $request->minutes * 60, $transition);

        return $this->present($request, $readiness, $level, $intensity, $transition, $items, array_values(array_unique($adjustments)));
    }

    /** Stage budgets in seconds, shifted towards mobility/activation for light or low-readiness sessions. */
    private function budgets(array $template, int $minutes, Intensity $intensity, ReadinessStatus $status): array
    {
        $table = $template['budgets'] ?? config('warmup.stage_budgets');
        $key = collect(array_keys($table))->sortBy(fn ($m) => abs($m - $minutes))->first();
        $budgets = $table[$key];

        // Scale if the template has no exact entry for the requested duration.
        if ($key !== $minutes) {
            $factor = $minutes / $key;
            $budgets = array_map(fn ($s) => (int) round($s * $factor), $budgets);
        }

        $shift = config("warmup.budget_shift.intensity.{$intensity->value}", 0)
            + config("warmup.budget_shift.readiness.{$status->value}", 0);

        if ($shift > 0) {
            $moved = (int) round($budgets['dynamic'] * min($shift, 0.8));
            $budgets['dynamic'] -= $moved;
            $budgets['mobility'] += intdiv($moved, 2);
            $budgets['activation'] += $moved - intdiv($moved, 2);
        } elseif ($shift < 0) {
            $moved = (int) round($budgets['mobility'] * min(abs($shift), 0.4));
            $budgets['mobility'] -= $moved;
            $budgets['dynamic'] += $moved;
        }

        return $budgets;
    }

    /**
     * @return array{0: ?Exercise, 1: ?string}
     */
    private function pick(array $candidates, Collection $exercises, array $ctx, array $used): array
    {
        $note = null;

        foreach ($candidates as $index => $slug) {
            /** @var Exercise|null $exercise */
            $exercise = $exercises->get($slug);

            if (! $exercise || isset($used[$slug])) {
                continue;
            }

            $reason = $this->unsuitableReason($exercise, $ctx);

            if ($reason === null) {
                return [$exercise, $index > 0 ? $note : null];
            }

            // Only explain substitutions the user would care about.
            if ($index === 0 && in_array($reason, ['equipment', 'readiness'], true)) {
                $note = $reason === 'equipment'
                    ? "{$exercise->name} skipped — needs ".$this->equipmentList($exercise->equipment).'.'
                    : "{$exercise->name} skipped today based on your readiness check.";
            }
        }

        return [null, $note];
    }

    private function unsuitableReason(Exercise $exercise, array $ctx): ?string
    {
        if ($exercise->min_level > $ctx['level'] || $exercise->min_intensity->rank() > $ctx['intensity']->rank()) {
            return 'level';
        }

        if ($exercise->impact->rank() > $ctx['maxImpact']->rank() || array_intersect($exercise->caution ?? [], $ctx['exclude'])) {
            return 'readiness';
        }

        if (array_diff($exercise->equipment ?? [], $ctx['equipment'])) {
            return 'equipment';
        }

        return null;
    }

    /**
     * Admit slots by priority until the stage budget is spent, then scale durations.
     *
     * @return list<array{slot:int, p:int, exercise: Exercise, seconds:int}>
     */
    private function fitStage(array $chosen, int $budget, int $transition, array &$used): array
    {
        $admitted = [];
        $spent = 0;

        foreach ($chosen as $entry) {
            // Elastic items (walking, jogging…) enter at their minimum and grow later.
            $exercise = $entry['exercise'];
            $seconds = $exercise->elastic ? $this->min($exercise) : $exercise->defaultSeconds();
            $cost = $seconds + $transition;

            if ($entry['p'] === 1 || $spent + $cost <= $budget + 10) {
                $admitted[] = [...$entry, 'seconds' => $seconds];
                $spent += $cost;
            } else {
                unset($used[$entry['exercise']->slug]);
            }
        }

        $admitted = $this->scale($admitted, $budget - count($admitted) * $transition);

        usort($admitted, fn ($a, $b) => $a['slot'] <=> $b['slot']);

        return $admitted;
    }

    /** Scale item durations towards a target (within each item's min/max). */
    private function scale(array $items, int $target): array
    {
        for ($pass = 0; $pass < 4 && $items; $pass++) {
            $total = array_sum(array_column($items, 'seconds'));
            $diff = $target - $total;

            if (abs($diff) < self::STEP) {
                break;
            }

            // Grow elastic items first; shrink non-elastic items first.
            $groups = $diff > 0
                ? [fn ($e) => $e->elastic, fn ($e) => true]
                : [fn ($e) => ! $e->elastic, fn ($e) => true];

            foreach ($groups as $filter) {
                $diff = $target - array_sum(array_column($items, 'seconds'));

                if (abs($diff) < self::STEP) {
                    break;
                }

                $keys = array_keys(array_filter($items, fn ($it) => $filter($it['exercise'])));
                $room = 0;

                foreach ($keys as $k) {
                    $room += $diff > 0
                        ? $this->max($items[$k]['exercise']) - $items[$k]['seconds']
                        : $items[$k]['seconds'] - $this->min($items[$k]['exercise']);
                }

                if ($room <= 0) {
                    continue;
                }

                $ratio = min(1, abs($diff) / $room);

                foreach ($keys as $k) {
                    $ex = $items[$k]['exercise'];
                    $delta = $diff > 0
                        ? ($this->max($ex) - $items[$k]['seconds']) * $ratio
                        : -($items[$k]['seconds'] - $this->min($ex)) * $ratio;
                    $items[$k]['seconds'] = $this->round($ex, $items[$k]['seconds'] + $delta);
                }
            }
        }

        // Still too long even at minimum durations: drop the least essential items.
        while (count($items) > 1 && array_sum(array_column($items, 'seconds')) > $target + 30) {
            $drop = collect($items)->sortBy([['p', 'desc'], ['slot', 'desc']])->keys()->first();

            if ($items[$drop]['p'] === 1) {
                break;
            }

            unset($items[$drop]);
            $items = array_values($items);
        }

        return $items;
    }

    /** Make the full routine (work + transitions) match the requested duration. */
    private function balance(array $stages, int $target, int $transition): array
    {
        $flat = [];

        foreach ($stages as $stage => $items) {
            foreach ($items as $item) {
                $flat[] = [...$item, 'stage' => $stage];
            }
        }

        if (! $flat) {
            return [];
        }

        $flat = $this->scale($flat, $target - count($flat) * $transition);

        // Very short sessions: if everything is already at its minimum, drop the least
        // essential items (keeping at least one exercise in every stage) and rescale.
        while ($this->total($flat, $transition) - $target > $this->slack($flat) && count($flat) > 4) {
            $perStage = array_count_values(array_column($flat, 'stage'));
            $drop = collect($flat)
                ->filter(fn ($it) => $perStage[$it['stage']] > 1 && ! $it['keep'])
                ->sortBy([
                    fn ($a, $b) => $b['p'] <=> $a['p'],
                    fn ($a, $b) => $perStage[$b['stage']] <=> $perStage[$a['stage']],
                    fn ($a, $b) => $b['slot'] <=> $a['slot'],
                ])
                ->keys()
                ->first();

            if ($drop === null) {
                break;
            }

            unset($flat[$drop]);
            $flat = array_values($flat);
            $flat = $this->scale($flat, $target - count($flat) * $transition);
        }

        // Absorb the remaining rounding difference, elastic items first.
        $order = collect($flat)
            ->sortByDesc(fn ($it) => ($it['exercise']->elastic ? 100000 : 0) + ($it['exercise']->per_side ? 0 : 10000) + $it['seconds'])
            ->keys();

        foreach ($order as $k) {
            $diff = $target - $this->total($flat, $transition);

            if ($diff === 0) {
                break;
            }

            $ex = $flat[$k]['exercise'];
            $step = $ex->per_side ? 10 : self::STEP;
            $wanted = $flat[$k]['seconds'] + intdiv($diff, $step) * $step;
            $flat[$k]['seconds'] = max($this->min($ex), min($this->max($ex), $wanted));
        }

        return $flat;
    }

    /** Seconds that can still be removed without going below each item's minimum. */
    private function slack(array $items): int
    {
        return array_sum(array_map(fn ($it) => $it['seconds'] - $this->min($it['exercise']), $items));
    }

    private function total(array $items, int $transition): int
    {
        return array_sum(array_column($items, 'seconds')) + count($items) * $transition;
    }

    private function min(Exercise $e): int
    {
        if ($e->elastic) {
            return 45;
        }

        $side = max(15, (int) (round($e->seconds * 0.6 / self::STEP) * self::STEP));

        return $e->per_side ? $side * 2 : max(20, $side);
    }

    private function max(Exercise $e): int
    {
        if ($e->elastic) {
            return 480;
        }

        return (int) (round($e->defaultSeconds() * 1.5 / 10) * 10);
    }

    private function round(Exercise $e, float $seconds): int
    {
        $step = $e->per_side ? self::STEP * 2 : self::STEP;
        $value = (int) (round($seconds / $step) * $step);

        return max($this->min($e), min($this->max($e), $value));
    }

    private function capAdjustments(RoutineRequest $request, int $level, Intensity $intensity, ReadinessStatus $status): array
    {
        $notes = [];

        if ($level < $request->level) {
            $notes[] = 'Level reduced to '.$level.' ('.Level::from($level)->label().') for today based on your readiness check.';
        }

        if ($intensity !== $request->intensity) {
            $notes[] = 'Intensity reduced to '.$intensity->label().' based on your readiness check.';
        }

        if ($status === ReadinessStatus::Caution) {
            $notes[] = 'High- and moderate-impact drills are removed today.';
        }

        return $notes;
    }

    private function equipmentList(array $keys): string
    {
        $labels = config('warmup.equipment');

        return collect($keys)->map(fn ($k) => Str::lower($labels[$k] ?? $k))->join(', ', ' and ');
    }

    private function present(
        RoutineRequest $request,
        ReadinessResult $readiness,
        int $level,
        Intensity $intensity,
        int $transition,
        array $items,
        array $adjustments,
    ): array {
        $flat = [];
        $stages = [];

        foreach (Stage::cases() as $stage) {
            $stageItems = array_values(array_filter($items, fn ($it) => $it['stage'] === $stage->value));

            if (! $stageItems) {
                continue;
            }

            $presented = array_map(function ($it) use ($stage, $intensity) {
                /** @var Exercise $ex */
                $ex = $it['exercise'];

                return [
                    'slug' => $ex->slug,
                    'name' => $ex->name,
                    'name_ar' => $ex->name_ar,
                    'stage' => $stage->value,
                    'stage_label' => $stage->label(),
                    'stage_number' => $stage->number(),
                    'seconds' => $it['seconds'],
                    'per_side' => $ex->per_side,
                    'side_seconds' => $ex->per_side ? intdiv($it['seconds'], 2) : null,
                    'cue' => $ex->cueFor($intensity),
                    'reps_label' => $ex->reps_label,
                    'exercise' => $ex->toClientArray(),
                ];
            }, $stageItems);

            $stages[] = [
                'key' => $stage->value,
                'number' => $stage->number(),
                'label' => $stage->label(),
                'description' => $stage->description(),
                'seconds' => array_sum(array_column($presented, 'seconds')) + count($presented) * $transition,
                'items' => $presented,
            ];

            array_push($flat, ...$presented);
        }

        $isFocus = in_array($request->program, ['knee', 'hip'], true);
        $name = $request->title
            ?? ($isFocus ? config("warmup.focus.{$request->program}.title") : $request->activity->shortLabel());

        return [
            'title' => implode(' | ', array_filter([
                $name,
                $request->minutes.' min',
                $intensity->label(),
                $readiness->status->label(),
            ])),
            'name' => $name,
            'program' => $request->program,
            'activity' => $request->activity->value,
            'activity_label' => $request->activity->label(),
            'emoji' => $isFocus ? config("warmup.focus.{$request->program}.emoji") : $request->activity->emoji(),
            'minutes' => $request->minutes,
            'intensity' => $intensity->value,
            'requested_intensity' => $request->intensity->value,
            'level' => $level,
            'level_label' => Level::from($level)->label(),
            'requested_level' => $request->level,
            'equipment' => array_values($request->equipment),
            'source' => $request->source,
            'transition_seconds' => $transition,
            'total_seconds' => array_sum(array_column($flat, 'seconds')) + count($flat) * $transition,
            'readiness' => $readiness->toArray(),
            'adjustments' => $adjustments,
            'stages' => $stages,
            'items' => $flat,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
