<?php

namespace App\Http\Controllers;

use App\Enums\Activity;
use App\Enums\Stage;
use App\Models\Exercise;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'tag' => (string) $request->query('tag', ''),
            'stage' => (string) $request->query('stage', ''),
            'activity' => (string) $request->query('activity', ''),
        ];

        $exercises = Exercise::with('tags')->ordered()->get();

        return view('exercises.index', [
            'exercises' => $exercises,
            'visible' => $exercises->filter(fn (Exercise $e) => $this->matches($e, $filters))->pluck('id')->flip(),
            'filters' => $filters,
            'tags' => Tag::orderBy('name')->get(),
            'stages' => Stage::cases(),
            'activities' => Activity::cases(),
        ]);
    }

    public function show(Exercise $exercise): View
    {
        $exercise->load('tags');

        $all = Exercise::ordered()->get(['id', 'slug', 'name', 'sort_order']);
        $index = $all->search(fn ($e) => $e->id === $exercise->id);

        return view('exercises.show', [
            'exercise' => $exercise,
            'previous' => $index > 0 ? $all[$index - 1] : null,
            'next' => $all[$index + 1] ?? null,
            'related' => Exercise::where('category', $exercise->category)
                ->whereKeyNot($exercise->id)
                ->ordered()
                ->limit(4)
                ->get(),
        ]);
    }

    /** Mirrors the client-side filter in resources/js/pages/library.js (used without JavaScript). */
    private function matches(Exercise $exercise, array $filters): bool
    {
        if ($filters['q'] !== '') {
            $haystack = mb_strtolower(implode(' ', [
                $exercise->name, $exercise->name_ar, $exercise->category, $exercise->purpose,
                implode(' ', $exercise->target_muscles), $exercise->tags->pluck('name')->implode(' '),
            ]));

            if (! str_contains($haystack, mb_strtolower($filters['q']))) {
                return false;
            }
        }

        if ($filters['tag'] !== '' && ! $exercise->tags->contains('slug', $filters['tag'])) {
            return false;
        }

        if ($filters['stage'] !== '' && $exercise->stage->value !== $filters['stage']) {
            return false;
        }

        return $filters['activity'] === '' || $exercise->isSuitableFor($filters['activity']);
    }
}
