<?php

namespace App\Services\Warmup;

/**
 * Answers from the daily readiness check. `checked = false` means the user
 * skipped the check (e.g. a Quick Warm-Up).
 */
final class ReadinessInput
{
    public const QUESTIONS = [
        'knee_pain' => 'Any knee pain today?',
        'knee_swelling' => 'Any knee swelling?',
        'knee_stiffness' => 'Any knee stiffness?',
        'instability' => 'Any feeling of instability / giving way?',
        'hip_glute_pain' => 'Any hip / glute pain?',
        'piriformis_pain' => 'Any pain around the piriformis area?',
        'walking_stairs_pain' => 'Any pain during walking or stairs?',
    ];

    public function __construct(
        public readonly bool $checked = false,
        public readonly bool $kneePain = false,
        public readonly bool $kneeSwelling = false,
        public readonly bool $kneeStiffness = false,
        public readonly bool $instability = false,
        public readonly bool $hipGlutePain = false,
        public readonly bool $piriformisPain = false,
        public readonly bool $walkingStairsPain = false,
        public readonly ?int $score = null,
    ) {}

    public static function unchecked(): self
    {
        return new self;
    }

    public static function fromArray(?array $data): self
    {
        if (! $data || ! ($data['checked'] ?? false)) {
            return self::unchecked();
        }

        return new self(
            checked: true,
            kneePain: (bool) ($data['knee_pain'] ?? false),
            kneeSwelling: (bool) ($data['knee_swelling'] ?? false),
            kneeStiffness: (bool) ($data['knee_stiffness'] ?? false),
            instability: (bool) ($data['instability'] ?? false),
            hipGlutePain: (bool) ($data['hip_glute_pain'] ?? false),
            piriformisPain: (bool) ($data['piriformis_pain'] ?? false),
            walkingStairsPain: (bool) ($data['walking_stairs_pain'] ?? false),
            score: isset($data['score']) ? max(1, min(10, (int) $data['score'])) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'checked' => $this->checked,
            'knee_pain' => $this->kneePain,
            'knee_swelling' => $this->kneeSwelling,
            'knee_stiffness' => $this->kneeStiffness,
            'instability' => $this->instability,
            'hip_glute_pain' => $this->hipGlutePain,
            'piriformis_pain' => $this->piriformisPain,
            'walking_stairs_pain' => $this->walkingStairsPain,
            'score' => $this->score,
        ];
    }
}
