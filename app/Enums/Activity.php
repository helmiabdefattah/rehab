<?php

namespace App\Enums;

/**
 * Training split chosen for the session. The whole app is organised around
 * which muscle group you are about to train — the warm-up and the workout are
 * both built from the split you pick.
 *
 * (Historically this enum modelled the activity type — gym/running/football.
 * It now models the push/pull/legs/cardio-&-core split.)
 */
enum Activity: string
{
    case Push = 'push';
    case Pull = 'pull';
    case Legs = 'legs';
    case CardioCore = 'cardio-core';

    public function label(): string
    {
        return match ($this) {
            self::Push => 'Push (Chest · Shoulders · Triceps)',
            self::Pull => 'Pull (Back · Biceps · Rear Delts)',
            self::Legs => 'Legs (Quads · Hamstrings · Glutes · Calves)',
            self::CardioCore => 'Cardio & Core',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Push => 'Push',
            self::Pull => 'Pull',
            self::Legs => 'Legs',
            self::CardioCore => 'Cardio & Core',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Push => '💪',
            self::Pull => '🪢',
            self::Legs => '🦵',
            self::CardioCore => '🫀',
        };
    }

    /** Muscles this split trains — used to explain why the warm-up targets them. */
    public function muscles(): string
    {
        return match ($this) {
            self::Push => 'chest, shoulders and triceps',
            self::Pull => 'back, biceps and rear deltoids',
            self::Legs => 'quadriceps, hamstrings, glutes and calves',
            self::CardioCore => 'heart, lungs and the whole core',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Push => 'Shoulders, scapulae and wrists primed for pressing — then the pressing work itself.',
            self::Pull => 'Lats, mid-back and biceps switched on for pulling — then the pulling work itself.',
            self::Legs => 'Ankles, hips and knees mobile and glutes firing — then squats, hinges and lunges.',
            self::CardioCore => 'Gradual rise in heart rate and a braced, switched-on core — then the conditioning work.',
        };
    }
}
