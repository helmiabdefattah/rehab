<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Checks every curated exercise video against YouTube's public oEmbed endpoint:
 * the link must resolve, and the returned title is shown next to the exercise so
 * a person can confirm the video matches.
 */
class VerifyVideos extends Command
{
    protected $signature = 'videos:verify {--timeout=10 : Seconds per request}';

    protected $description = 'Verify that every exercise video URL resolves on YouTube and show its real title/channel';

    public function handle(): int
    {
        $rows = [];
        $failed = 0;

        foreach (Exercise::ordered()->get() as $exercise) {
            if (! $exercise->hasVideo()) {
                $rows[] = [$exercise->slug, '—', 'NO VIDEO (search fallback)', ''];

                continue;
            }

            try {
                $response = Http::timeout((int) $this->option('timeout'))
                    ->acceptJson()
                    ->get('https://www.youtube.com/oembed', ['url' => $exercise->video_url, 'format' => 'json']);

                if ($response->successful()) {
                    $title = (string) $response->json('title');
                    $match = $exercise->video_title && mb_strtolower($title) === mb_strtolower($exercise->video_title) ? 'OK' : 'OK — title differs, review';
                    $rows[] = [$exercise->slug, $exercise->videoId(), $match, $title.' · '.$response->json('author_name')];
                } else {
                    $failed++;
                    $rows[] = [$exercise->slug, $exercise->videoId(), 'FAILED (HTTP '.$response->status().')', $exercise->video_url];
                }
            } catch (Throwable $e) {
                $failed++;
                $rows[] = [$exercise->slug, $exercise->videoId(), 'ERROR', $e->getMessage()];
            }
        }

        $this->table(['Exercise', 'Video ID', 'Result', 'YouTube title · channel'], $rows);

        if ($failed) {
            $this->error("{$failed} video link(s) failed. Replace them with: php artisan videos:set {slug} {url}");

            return self::FAILURE;
        }

        $this->info('All curated video links resolved. Check that each title matches its exercise.');

        return self::SUCCESS;
    }
}
