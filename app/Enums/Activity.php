<?php

namespace App\Enums;

enum Activity: string
{
    case Gym = 'gym';
    case Running = 'running';
    case Football = 'football';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Gym => 'Gym / Strength Training',
            self::Running => 'Running',
            self::Football => 'Football',
            self::General => 'General Exercise',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Gym => 'Gym',
            self::Running => 'Running',
            self::Football => 'Football',
            self::General => 'General',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Gym => '🏋️',
            self::Running => '🏃',
            self::Football => '⚽',
            self::General => '🚶',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Gym => 'General movement → mobility → activation → pattern rehearsal → warm-up sets',
            self::Running => 'Walk → mobility → calves & glutes → marching → skipping → easy jog → build-ups',
            self::Football => 'Walk → mobility → glutes → lateral movement → acceleration → change of direction → ball',
            self::General => 'Balanced low-impact preparation for classes, hiking, sport or everyday activity',
        };
    }
}
