<?php

namespace App\Enums;

enum Impact: string
{
    case Low = 'Low';
    case Moderate = 'Moderate';
    case High = 'High';

    public function rank(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Moderate => 2,
            self::High => 3,
        };
    }
}
