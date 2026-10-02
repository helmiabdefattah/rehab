<?php

namespace App\Http\Controllers;

use App\Enums\Activity;
use App\Enums\Intensity;
use App\Enums\Level;
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

    /** ⚡ Quick modes launch a balanced full-body warm-up straight into Workout Mode. */
    public function quick(Request $request, RoutineBuilder $builder, int $minutes): View
    {
        $quick = config("warmup.quick.{$minutes}");
        $prefs = WarmupPreferences::fromRequest($request);

        $routine = $builder->build(new RoutineRequest(
            program: $quick['program'],
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

    private function builderView(array $data = []): View
    {
        return view('warmup.builder', [
            'activities' => Activity::cases(),
            'durations' => config('warmup.durations'),
            'intensities' => Intensity::cases(),
            'levels' => Level::cases(),
            'equipment' => config('warmup.equipment'),
            'preselected' => null,
            'routine' => null,
            'autostart' => false,
            ...$data,
        ]);
    }
}
