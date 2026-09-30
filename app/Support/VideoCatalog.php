<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Reads and writes database/data/videos.php (the curated video list).
 */
class VideoCatalog
{
    public const ID_PATTERN = '~(?:youtube\.com/watch\?(?:.*&)?v=|youtu\.be/|youtube\.com/(?:shorts|embed)/)([A-Za-z0-9_-]{11})~';

    public function __construct(private readonly ?string $path = null) {}

    public function path(): string
    {
        return $this->path ?? database_path('data/videos.php');
    }

    /** @return array<string, array<string, ?string>> */
    public function all(): array
    {
        return require $this->path();
    }

    public static function videoId(string $url): ?string
    {
        return preg_match(self::ID_PATTERN, $url, $m) ? $m[1] : null;
    }

    public function set(string $slug, array $video): void
    {
        if (! self::videoId($video['url'] ?? '')) {
            throw new InvalidArgumentException('Not a YouTube video URL: '.($video['url'] ?? ''));
        }

        $all = $this->all();
        $all[$slug] = array_filter([
            'url' => $video['url'],
            'title' => $video['title'] ?? null,
            'channel' => $video['channel'] ?? null,
            'source_type' => $video['source_type'] ?? 'other',
            'verification' => $video['verification'] ?? 'confirmed',
            'note' => $video['note'] ?? null,
        ], fn ($v, $k) => $v !== null || in_array($k, ['title', 'channel'], true), ARRAY_FILTER_USE_BOTH);

        $this->write($all);
    }

    private function write(array $all): void
    {
        $source = file_get_contents($this->path());
        $header = substr($source, 0, strpos($source, 'return ['));

        $body = "return [\n";
        foreach ($all as $slug => $video) {
            $body .= '    '.var_export($slug, true)." => [\n";
            foreach ($video as $key => $value) {
                $body .= '        '.var_export($key, true).' => '.($value === null ? 'null' : var_export($value, true)).",\n";
            }
            $body .= "    ],\n";
        }
        $body .= "];\n";

        file_put_contents($this->path(), $header.$body);
    }
}
