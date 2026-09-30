<?php

namespace App\Enums;

enum ReadinessStatus: string
{
    case Ready = 'ready';
    case Modify = 'modify';
    case Caution = 'caution';
    case Unchecked = 'unchecked';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Good readiness',
            self::Modify => 'Take it steady',
            self::Caution => 'Caution',
            self::Unchecked => 'Readiness not checked',
        };
    }
}
