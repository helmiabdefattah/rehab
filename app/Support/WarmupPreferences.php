<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Preferences the browser keeps in localStorage and passes along as query
 * parameters (level, equipment, transition) for server-built routines.
 */
final class WarmupPreferences
{
    public function __construct(
        public readonly int $level = 1,
        public readonly array $equipment = [],
        public readonly int $transition = 5,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $known = array_keys(config('warmup.equipment'));
        $equipment = $request->has('equipment')
            ? array_values(array_intersect($known, explode(',', (string) $request->query('equipment'))))
            : config('warmup.default_equipment');

        return new self(
            level: max(1, min(3, (int) $request->query('level', 1))),
            equipment: $equipment,
            transition: max(0, min(15, (int) $request->query('transition', config('warmup.transition_seconds')))),
        );
    }
}
