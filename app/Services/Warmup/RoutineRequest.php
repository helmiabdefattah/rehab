<?php

namespace App\Services\Warmup;

use App\Enums\Activity;
use App\Enums\Intensity;

final class RoutineRequest
{
    /**
     * @param  string  $program  template key: gym|running|football|general|knee|hip
     * @param  list<string>  $equipment  equipment keys available today
     * @param  string  $source  builder|quick|focus
     */
    public function __construct(
        public readonly string $program,
        public readonly Activity $activity,
        public readonly int $minutes,
        public readonly Intensity $intensity,
        public readonly int $level,
        public readonly array $equipment,
        public readonly ReadinessInput $readiness,
        public readonly int $transitionSeconds = 5,
        public readonly string $source = 'builder',
        public readonly ?string $title = null,
    ) {}
}
