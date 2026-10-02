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
            'sets' => 'array',
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

    public function scopeWarmups(Builder $query): Builder
    {
        return $query->where('section', 'warmup');
    }

    public function scopeWorkouts(Builder $query): Builder
    {
        return $query->where('section', 'workout');
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

    public function isWorkout(): bool
    {
        return $this->section === 'workout';
    }

    /** Which training splits this exercise belongs to (push|pull|legs|cardio-core). */
    public function splits(): array
    {
        return $this->activities ?? [];
    }

    public function isSuitableFor(string $split): bool
    {
        return in_array($split, $this->activities ?? [], true);
    }

    public function cueFor(Intensity $intensity): ?string
    {
        return $this->cues[$intensity->value] ?? null;
    }

    /** The looping SVG movement pattern used instead of a video. */
    public function animationPattern(): string
    {
        return $this->animation ?: 'generic';
    }

    /** Data used by the animation viewer (replaces the old video payload). */
    public function animationPayload(): array
    {
        return [
            'exercise' => $this->name,
            'name_ar' => $this->name_ar,
            'pattern' => $this->animationPattern(),
            'stage' => $this->stage->value,
            'reps' => $this->reps_label,
        ];
    }

    /** Everything the front-end needs to render a card / run Workout Mode. */
    public function toClientArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'section' => $this->section,
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
            'splits' => $this->splits(),
            'sets' => $this->sets,
            'instructions' => $this->instructions,
            'mistakes' => $this->mistakes,
            'safety' => $this->safety,
            'progression' => $this->progression,
            'regression' => $this->regression,
            'tags' => $this->relationLoaded('tags') ? $this->tags->pluck('name')->all() : [],
            'url' => route('exercises.show', $this),
            'animation' => $this->animationPayload(),
        ];
    }
}
