<?php

namespace App\Http\Controllers;

use App\Enums\Activity;
use App\Enums\Intensity;
use App\Enums\Level;
use App\Models\Exercise;
use App\Services\Warmup\ReadinessInput;
use App\Services\Warmup\RoutineBuilder;
use App\Services\Warmup\RoutineRequest;
use App\Support\WarmupPreferences;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarmupController extends Controller
{
    public function builder(Request $request): View
    {
        return $this->builderView([
            'preselected' => Activity::tryFrom((string) $request->query('activity'))?->value,
        ]);
    }

    /** ⚡ Quick modes launch a balanced routine straight into Workout Mode. */
    public function quick(Request $request, RoutineBuilder $builder, int $minutes): View
    {
        $quick = config("warmup.quick.{$minutes}");
        $prefs = WarmupPreferences::fromRequest($request);

        $routine = $builder->build(new RoutineRequest(
            program: $quick['activity'],
            activity: Activity::from($quick['activity']),
            minutes: $minutes,
            intensity: Intensity::from($quick['intensity']),
            level: $prefs->level,
            equipment: $prefs->equipment,
            readiness: ReadinessInput::unchecked(),
            transitionSeconds: $prefs->transition,
            source: 'quick',
            title: $quick['title'],
        ));

        return $this->builderView(['routine' => $routine, 'autostart' => true]);
    }

    public function focus(string $area): View
    {
        $exercises = Exercise::with('tags')
            ->ordered()
            ->get()
            ->filter(fn (Exercise $e) => in_array($area, $e->focus, true));

        return view('warmup.focus', [
            'area' => $area,
            'config' => config("warmup.focus.{$area}"),
            'exercises' => $exercises,
        ]);
    }

    public function startFocus(Request $request, RoutineBuilder $builder, string $area, int $minutes): View
    {
        $prefs = WarmupPreferences::fromRequest($request);

        $routine = $builder->build(new RoutineRequest(
            program: $area,
            activity: Activity::General,
            minutes: $minutes,
            intensity: Intensity::Moderate,
            level: $prefs->level,
            equipment: $prefs->equipment,
            readiness: ReadinessInput::unchecked(),
            transitionSeconds: $prefs->transition,
            source: 'focus',
        ));

        return $this->builderView(['routine' => $routine, 'autostart' => true]);
    }

    private function builderView(array $data = []): View
    {
        return view('warmup.builder', [
            'activities' => Activity::cases(),
            'durations' => config('warmup.durations'),
            'intensities' => Intensity::cases(),
            'levels' => Level::cases(),
            'equipment' => config('warmup.equipment'),
            'questions' => ReadinessInput::QUESTIONS,
            'preselected' => null,
            'routine' => null,
            'autostart' => false,
            ...$data,
        ]);
    }
}
