<?php

namespace App\Enums;

enum Intensity: string
{
    case Light = 'light';
    case Moderate = 'moderate';
    case High = 'high';

    public function rank(): int
    {
        return match ($this) {
            self::Light => 1,
            self::Moderate => 2,
            self::High => 3,
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public static function fromRank(int $rank): self
    {
        return match (max(1, min(3, $rank))) {
            1 => self::Light,
            2 => self::Moderate,
            3 => self::High,
        };
    }

    public function min(self $other): self
    {
        return $this->rank() <= $other->rank() ? $this : $other;
    }
}
