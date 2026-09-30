<?php

namespace App\Enums;

enum Level: int
{
    case ReEntry = 1;
    case Conditioning = 2;
    case Performance = 3;

    public function label(): string
    {
        return match ($this) {
            self::ReEntry => 'Re-entry',
            self::Conditioning => 'Conditioning',
            self::Performance => 'Performance Preparation',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ReEntry => 'Low impact and controlled movements.',
            self::Conditioning => 'Moderate dynamic movements and greater range of motion.',
            self::Performance => 'More dynamic movements, controlled acceleration, lateral movement and activity-specific preparation.',
        };
    }
}
