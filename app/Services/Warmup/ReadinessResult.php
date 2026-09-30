<?php

namespace App\Services\Warmup;

use App\Enums\Impact;
use App\Enums\Intensity;
use App\Enums\ReadinessStatus;

final class ReadinessResult
{
    /**
     * @param  list<string>  $messages  what was noticed and how today's routine changes
     * @param  list<string>  $advice  general next steps (never a diagnosis)
     * @param  list<string>  $excludeGroups  symptom groups ('knee', 'hip') used to drop exercises
     * @param  list<string>  $reported  human-readable list of what the user reported
     */
    public function __construct(
        public readonly ReadinessStatus $status,
        public readonly ?int $score,
        public readonly string $headline,
        public readonly array $messages,
        public readonly array $advice,
        public readonly int $maxLevel,
        public readonly Intensity $maxIntensity,
        public readonly Impact $maxImpact,
        public readonly array $excludeGroups,
        public readonly array $reported,
        public readonly ReadinessInput $input,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'label' => $this->status->label(),
            'score' => $this->score,
            'headline' => $this->headline,
            'messages' => $this->messages,
            'advice' => $this->advice,
            'max_level' => $this->maxLevel,
            'max_intensity' => $this->maxIntensity->value,
            'max_impact' => $this->maxImpact->value,
            'exclude_groups' => $this->excludeGroups,
            'reported' => $this->reported,
            'answers' => $this->input->toArray(),
        ];
    }
}
