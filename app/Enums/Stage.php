<?php

namespace App\Enums;

enum Stage: string
{
    case Heat = 'heat';
    case Mobility = 'mobility';
    case Activation = 'activation';
    case Dynamic = 'dynamic';

    public function number(): int
    {
        return match ($this) {
            self::Heat => 1,
            self::Mobility => 2,
            self::Activation => 3,
            self::Dynamic => 4,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Heat => 'General Heat',
            self::Mobility => 'Dynamic Mobility',
            self::Activation => 'Activation & Stability',
            self::Dynamic => 'Dynamic & Sport Preparation',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Heat => 'Low-impact movement to raise body temperature and heart rate.',
            self::Mobility => 'Controlled movement through comfortable ranges — no aggressive end-range stretching.',
            self::Activation => 'Wake up the glutes, knee-supporting muscles and core. Quality over quantity.',
            self::Dynamic => 'Rehearse the movements of your activity, adding speed and impact gradually.',
        };
    }
}
