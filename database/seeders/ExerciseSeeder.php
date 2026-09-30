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
        $videos = require database_path('data/videos.php');

        foreach ($exercises as $index => $data) {
            $video = $videos[$data['slug']] ?? null;
            $tags = $data['tags'];

            if ($data['impact'] === 'Low') {
                $tags[] = 'Low Impact';
            }

            unset($data['tags']);

            $exercise = Exercise::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'cues' => $data['cues'] ?? null,
                    'video_url' => $video['url'] ?? null,
                    'video_title' => $video['title'] ?? null,
                    'video_channel' => $video['channel'] ?? null,
                    'video_source_type' => $video['source_type'] ?? null,
                    'video_verification' => $video['verification'] ?? null,
                    'video_note' => $video['note'] ?? null,
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
