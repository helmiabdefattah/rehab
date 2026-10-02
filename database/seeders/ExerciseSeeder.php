<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExerciseSeeder extends Seeder
{
    /**
     * Idempotent: re-running updates exercises in place (matched by slug).
     */
    public function run(): void
    {
        $exercises = require database_path('data/exercises.php');

        foreach ($exercises as $index => $data) {
            $tags = $data['tags'];

            if ($data['impact'] === 'Low') {
                $tags[] = 'Low Impact';
            }

            unset($data['tags']);

            $exercise = Exercise::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'section' => $data['section'] ?? 'warmup',
                    'cues' => $data['cues'] ?? null,
                    'sets' => $data['sets'] ?? null,
                    'sort_order' => $index + 1,
                ],
            );

            $tagIds = collect(array_unique($tags))
                ->map(fn (string $name) => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id);

            $exercise->tags()->sync($tagIds);
        }

        Exercise::whereNotIn('slug', array_column($exercises, 'slug'))->delete();
    }
}
