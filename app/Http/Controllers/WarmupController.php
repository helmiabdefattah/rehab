<?php

namespace App\Http\Controllers;

use App\Enums\Activity;
use App\Enums\Intensity;
use App\Enums\Level;
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

    /**
     * ⚡ Quick modes launch a balanced full-body warm-up straight into Workout
     * Mode. The routine is built in the browser (offline-capable), so this
     * only needs to render the shell with the chosen duration.
     */
    public function quick(int $minutes): View
    {
        return $this->builderView(['quick' => $minutes, 'autostart' => true]);
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
            'quick' => null,
            'routine' => null,
            'autostart' => false,
            ...$data,
        ]);
    }
}
