<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Support\VideoCatalog;
use Illuminate\Console\Command;
use InvalidArgumentException;

class SetVideo extends Command
{
    protected $signature = 'videos:set
        {slug : Exercise slug, e.g. calf-raises}
        {url : YouTube watch/shorts URL you have checked}
        {--title= : Video title as shown on YouTube}
        {--channel= : Channel / creator}
        {--source=other : physiotherapy|medical-organization|strength-conditioning|fitness-education|running-coach|football-organization|other}
        {--note= : Optional note shown under the video}';

    protected $description = 'Set or replace the demonstration video for an exercise (updates database/data/videos.php and the database)';

    public function handle(VideoCatalog $catalog): int
    {
        $exercise = Exercise::where('slug', $this->argument('slug'))->first();

        if (! $exercise) {
            $this->error('Unknown exercise slug. Available: '.Exercise::ordered()->pluck('slug')->implode(', '));

            return self::FAILURE;
        }

        $video = [
            'url' => $this->argument('url'),
            'title' => $this->option('title'),
            'channel' => $this->option('channel'),
            'source_type' => $this->option('source'),
            'verification' => 'confirmed',
            'note' => $this->option('note'),
        ];

        try {
            $catalog->set($exercise->slug, $video);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $exercise->update([
            'video_url' => $video['url'],
            'video_title' => $video['title'],
            'video_channel' => $video['channel'],
            'video_source_type' => $video['source_type'],
            'video_verification' => 'confirmed',
            'video_note' => $video['note'],
        ]);

        $this->info("Video for {$exercise->name} saved. Run `php artisan videos:verify` to check it resolves.");

        return self::SUCCESS;
    }
}
