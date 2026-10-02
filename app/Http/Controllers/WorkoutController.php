<?php

namespace App\Http\Controllers;

use App\Enums\Activity;
use App\Models\Exercise;
use Illuminate\View\View;

/**
 * The split hub: one page per training split with the targeted warm-up entry
 * and the workout itself (the ordered training exercises for that split).
 */
class WorkoutController extends Controller
{
    public function __invoke(string $split): View
    {
        $activity = Activity::from($split);
        $slugs = config("warmup.workouts.{$split}", []);

        $exercises = Exercise::with('tags')
            ->whereIn('slug', $slugs)
            ->get()
            ->sortBy(fn (Exercise $e) => array_search($e->slug, $slugs))
            ->values();

        return view('workout.show', [
            'split' => $split,
            'activity' => $activity,
            'exercises' => $exercises,
        ]);
    }
}
