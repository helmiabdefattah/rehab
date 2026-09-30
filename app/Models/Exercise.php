<?php

namespace App\Models;

use App\Enums\Impact;
use App\Enums\Intensity;
use App\Enums\Stage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exercise extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'stage' => Stage::class,
            'impact' => Impact::class,
            'min_intensity' => Intensity::class,
            'min_level' => 'integer',
            'seconds' => 'integer',
            'per_side' => 'boolean',
            'elastic' => 'boolean',
            'focus' => 'array',
            'target_muscles' => 'array',
            'target_joints' => 'array',
            'instructions' => 'array',
            'mistakes' => 'array',
            'safety' => 'array',
            'activities' => 'array',
            'equipment' => 'array',
            'caution' => 'array',
            'cues' => 'array',
        ];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Default work time for one pass of the exercise (both sides when per-side). */
    public function defaultSeconds(): int
    {
        return $this->per_side ? $this->seconds * 2 : $this->seconds;
    }

    public function hasVideo(): bool
    {
        return $this->videoId() !== null;
    }

    public function videoId(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $patterns = [
            '~youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})~',
            '~youtu\.be/([A-Za-z0-9_-]{11})~',
            '~youtube\.com/(?:shorts|embed)/([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->video_url, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public function embedUrl(): ?string
    {
        $id = $this->videoId();

        return $id ? "https://www.youtube-nocookie.com/embed/{$id}?rel=0&modestbranding=1&playsinline=1" : null;
    }

    public function thumbnailUrl(): ?string
    {
        $id = $this->videoId();

        return $id ? "https://i.ytimg.com/vi/{$id}/hqdefault.jpg" : null;
    }

    /** Fallback when no curated video exists: a YouTube *search* (never an invented video URL). */
    public function videoSearchUrl(): string
    {
        return 'https://www.youtube.com/results?search_query='.rawurlencode($this->name.' exercise physical therapist');
    }

    public function cueFor(Intensity $intensity): ?string
    {
        return $this->cues[$intensity->value] ?? null;
    }

    public function isSuitableFor(string $activity): bool
    {
        return in_array($activity, $this->activities ?? [], true);
    }

    /** Everything the front-end needs to render a card / run Workout Mode. */
    public function toClientArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'stage' => $this->stage->value,
            'category' => $this->category,
            'purpose' => $this->purpose,
            'why' => $this->why,
            'difficulty' => $this->difficulty,
            'impact' => $this->impact->value,
            'target' => implode(' / ', $this->target_muscles),
            'target_joints' => $this->target_joints,
            'duration_label' => $this->duration_label,
            'reps_label' => $this->reps_label,
            'per_side' => $this->per_side,
            'instructions' => $this->instructions,
            'mistakes' => $this->mistakes,
            'safety' => $this->safety,
            'progression' => $this->progression,
            'regression' => $this->regression,
            'tags' => $this->relationLoaded('tags') ? $this->tags->pluck('name')->all() : [],
            'url' => route('exercises.show', $this),
            'video' => $this->videoPayload(),
        ];
    }

    /** Data used by the video modal (Watch Video / Find a video). */
    public function videoPayload(): array
    {
        return [
            'exercise' => $this->name,
            'id' => $this->videoId(),
            'url' => $this->video_url,
            'embed' => $this->embedUrl(),
            'thumbnail' => $this->thumbnailUrl(),
            'title' => $this->video_title,
            'channel' => $this->video_channel,
            'verification' => $this->video_verification,
            'note' => $this->video_note,
            'search' => $this->videoSearchUrl(),
        ];
    }
}
