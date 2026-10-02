<?php

/*
|--------------------------------------------------------------------------
| Warm-up & workout engine configuration
|--------------------------------------------------------------------------
|
| The app is organised around the training SPLIT you pick (push | pull |
| legs | cardio-core). For each split there are two parts:
|
|   • a targeted WARM-UP, built from the templates below, that primes the
|     exact muscles and joints you are about to train; and
|   • the WORKOUT itself — the ordered list of training exercises in
|     'workouts' below.
|
| Warm-up templates describe WHICH exercises may fill each stage and in what
| order. The RoutineBuilder decides WHICH candidate is used (level, intensity,
| impact, equipment) and HOW LONG each item runs so the routine fits the
| chosen time.
|
| Slot format:
|   'pick'   => candidate slugs in order of preference
|   'levels' => [level => [slugs]]  optional override of 'pick' for a level
|   'p'      => priority 1–4 (1 = always included, 4 = only when time allows)
|   'keep'   => true: never trimmed from very short routines
*/

return [

    'durations' => [5, 10, 15, 20],

    // Seconds between exercises in Workout Mode ("get ready" countdown).
    'transition_seconds' => 5,

    // Seconds per stage (heat, mobility, activation, dynamic) for each duration.
    'stage_budgets' => [
        5 => ['heat' => 60, 'mobility' => 90, 'activation' => 90, 'dynamic' => 60],
        10 => ['heat' => 120, 'mobility' => 150, 'activation' => 180, 'dynamic' => 150],
        15 => ['heat' => 150, 'mobility' => 240, 'activation' => 270, 'dynamic' => 240],
        20 => ['heat' => 180, 'mobility' => 300, 'activation' => 330, 'dynamic' => 390],
    ],

    // Share of the dynamic-stage budget moved to mobility/activation for lighter sessions.
    'budget_shift' => [
        'intensity' => ['light' => 0.20, 'moderate' => 0.0, 'high' => -0.15],
        'readiness' => ['ready' => 0.0, 'unchecked' => 0.0, 'modify' => 0.25, 'caution' => 0.50],
    ],

    'equipment' => [
        'mini-band' => 'Mini band (loop)',
        'long-band' => 'Long resistance band',
        'step' => 'Step / box / stairs',
        'bike' => 'Stationary bike',
    ],

    'default_equipment' => ['mini-band', 'long-band', 'step'],

    // ⚡ Quick modes run a balanced full-body warm-up straight into Workout Mode.
    'quick' => [
        5 => ['title' => '5-Min Express', 'activity' => 'cardio-core', 'program' => 'full-body', 'intensity' => 'moderate'],
        10 => ['title' => '10-Min Standard', 'activity' => 'cardio-core', 'program' => 'full-body', 'intensity' => 'moderate'],
        15 => ['title' => '15-Min Complete', 'activity' => 'cardio-core', 'program' => 'full-body', 'intensity' => 'moderate'],
    ],

    // The workout itself, per split — ordered training exercises.
    'workouts' => [
        'push' => ['barbell-bench-press', 'overhead-press', 'incline-dumbbell-press', 'chest-dip', 'triceps-pushdown', 'dumbbell-lateral-raise'],
        'pull' => ['pull-up', 'lat-pulldown', 'barbell-row', 'seated-cable-row', 'face-pull', 'dumbbell-biceps-curl'],
        'legs' => ['back-squat', 'romanian-deadlift', 'leg-press', 'walking-lunge', 'lying-leg-curl', 'standing-calf-raise'],
        'cardio-core' => ['treadmill-intervals', 'rowing-machine', 'hanging-leg-raise', 'cable-crunch', 'russian-twist', 'plank-hold'],
    ],

    'templates' => [

        /*
         | PUSH — prime the shoulders, scapulae and wrists, switch on the
         | rotator cuff and upper back, then rehearse the pressing pattern.
         */
        'push' => [
            'budgets' => [
                5 => ['heat' => 45, 'mobility' => 120, 'activation' => 105, 'dynamic' => 30],
                10 => ['heat' => 90, 'mobility' => 240, 'activation' => 210, 'dynamic' => 60],
                15 => ['heat' => 120, 'mobility' => 330, 'activation' => 300, 'dynamic' => 150],
                20 => ['heat' => 150, 'mobility' => 420, 'activation' => 390, 'dynamic' => 240],
            ],
            'heat' => [
                ['pick' => ['brisk-walking', 'marching-in-place'], 'p' => 1],
                ['pick' => ['arm-circles'], 'p' => 2],
            ],
            'mobility' => [
                ['pick' => ['shoulder-pass-through'], 'p' => 1],
                ['pick' => ['wall-slides'], 'p' => 1],
                ['pick' => ['wrist-forearm-circles'], 'p' => 2],
                ['pick' => ['cat-cow'], 'p' => 3],
                ['pick' => ['open-book'], 'p' => 3],
            ],
            'activation' => [
                ['pick' => ['band-pull-apart'], 'p' => 1],
                ['pick' => ['band-external-rotation'], 'p' => 1],
                ['pick' => ['scapular-push-up'], 'p' => 2],
            ],
            'dynamic' => [
                ['pick' => ['ramp-up-sets'], 'p' => 1, 'keep' => true],
            ],
        ],

        /*
         | PULL — open the thoracic spine and shoulders, switch on the lats,
         | mid-back and rear delts, then rehearse the pulling pattern.
         */
        'pull' => [
            'budgets' => [
                5 => ['heat' => 45, 'mobility' => 120, 'activation' => 105, 'dynamic' => 30],
                10 => ['heat' => 90, 'mobility' => 240, 'activation' => 210, 'dynamic' => 60],
                15 => ['heat' => 120, 'mobility' => 330, 'activation' => 300, 'dynamic' => 150],
                20 => ['heat' => 150, 'mobility' => 420, 'activation' => 390, 'dynamic' => 240],
            ],
            'heat' => [
                ['pick' => ['brisk-walking', 'marching-in-place'], 'p' => 1],
                ['pick' => ['arm-circles'], 'p' => 2],
            ],
            'mobility' => [
                ['pick' => ['cat-cow'], 'p' => 1],
                ['pick' => ['shoulder-pass-through'], 'p' => 1],
                ['pick' => ['open-book'], 'p' => 2],
                ['pick' => ['wrist-forearm-circles'], 'p' => 3],
            ],
            'activation' => [
                ['pick' => ['band-pull-apart'], 'p' => 1],
                ['pick' => ['scapular-pull'], 'p' => 1],
                ['pick' => ['band-external-rotation'], 'p' => 2],
            ],
            'dynamic' => [
                ['pick' => ['ramp-up-sets'], 'p' => 1, 'keep' => true],
            ],
        ],

        /*
         | LEGS — mobilise ankles, hips and knees, fire up the glutes and
         | knee-supporting muscles, then rehearse squat and hinge patterns.
         */
        'legs' => [
            'heat' => [
                ['pick' => ['easy-cycling', 'brisk-walking', 'marching-in-place'], 'p' => 1],
                ['pick' => ['marching-in-place'], 'p' => 3],
            ],
            'mobility' => [
                ['pick' => ['ankle-rocks'], 'p' => 1],
                ['pick' => ['standing-hip-cars'], 'p' => 1],
                ['pick' => ['leg-swings'], 'p' => 2],
                ['pick' => ['hamstring-scoops'], 'p' => 2],
                ['pick' => ['hip-flexor-rock'], 'p' => 3],
                ['pick' => ['hip-90-90', 'figure-4-rocks'], 'p' => 3],
                ['pick' => ['adductor-rockback'], 'p' => 4],
            ],
            'activation' => [
                ['pick' => ['banded-glute-bridge', 'glute-bridge'], 'p' => 1],
                ['pick' => ['clamshell', 'side-lying-hip-abduction'], 'p' => 2],
                ['pick' => ['calf-raises'], 'p' => 2],
                ['pick' => ['lateral-band-walk', 'monster-walk'], 'p' => 3],
                ['pick' => ['single-leg-balance'], 'p' => 4],
            ],
            'dynamic' => [
                ['pick' => ['mini-squat', 'sit-to-stand'], 'levels' => [1 => ['sit-to-stand', 'mini-squat']], 'p' => 1],
                ['pick' => ['hip-hinge'], 'p' => 1],
                ['pick' => ['reverse-lunge', 'step-up'], 'p' => 3],
                ['pick' => ['ramp-up-sets'], 'p' => 1, 'keep' => true],
            ],
        ],

        /*
         | CARDIO & CORE — raise the heart rate gradually, mobilise the spine
         | and hips, brace the core, then build running mechanics and pace.
         */
        'cardio-core' => [
            'budgets' => [
                5 => ['heat' => 60, 'mobility' => 75, 'activation' => 75, 'dynamic' => 90],
                10 => ['heat' => 90, 'mobility' => 135, 'activation' => 150, 'dynamic' => 225],
                15 => ['heat' => 120, 'mobility' => 210, 'activation' => 240, 'dynamic' => 330],
                20 => ['heat' => 150, 'mobility' => 270, 'activation' => 300, 'dynamic' => 480],
            ],
            'heat' => [
                ['pick' => ['brisk-walking', 'marching-in-place'], 'p' => 1],
                ['pick' => ['arm-circles'], 'p' => 3],
            ],
            'mobility' => [
                ['pick' => ['standing-hip-cars'], 'p' => 1],
                ['pick' => ['leg-swings'], 'p' => 1],
                ['pick' => ['open-book'], 'p' => 2],
                ['pick' => ['cat-cow'], 'p' => 2],
                ['pick' => ['ankle-rocks'], 'p' => 3],
            ],
            'activation' => [
                ['pick' => ['dead-bug'], 'p' => 1],
                ['pick' => ['bird-dog'], 'p' => 1],
                ['pick' => ['glute-bridge', 'banded-glute-bridge'], 'p' => 2],
                ['pick' => ['forearm-plank'], 'p' => 2],
                ['pick' => ['side-plank'], 'p' => 3],
            ],
            'dynamic' => [
                ['pick' => ['a-march', 'marching-in-place'], 'p' => 1],
                ['pick' => ['a-skip'], 'p' => 2],
                ['pick' => ['easy-jog', 'brisk-walking'], 'p' => 1],
                ['pick' => ['build-up-runs'], 'p' => 2],
                ['pick' => ['lateral-shuffle'], 'p' => 3],
            ],
        ],

        /*
         | FULL-BODY — the balanced routine used by the ⚡ Quick warm-ups.
         */
        'full-body' => [
            'heat' => [
                ['pick' => ['marching-in-place', 'brisk-walking'], 'p' => 1],
            ],
            'mobility' => [
                ['pick' => ['standing-hip-cars'], 'p' => 1],
                ['pick' => ['arm-circles'], 'p' => 1],
                ['pick' => ['leg-swings'], 'p' => 2],
                ['pick' => ['open-book'], 'p' => 2],
                ['pick' => ['ankle-rocks'], 'p' => 3],
            ],
            'activation' => [
                ['pick' => ['glute-bridge', 'banded-glute-bridge'], 'p' => 1],
                ['pick' => ['dead-bug'], 'p' => 2],
                ['pick' => ['band-pull-apart'], 'p' => 2],
                ['pick' => ['calf-raises'], 'p' => 3],
                ['pick' => ['clamshell'], 'p' => 3],
            ],
            'dynamic' => [
                ['pick' => ['a-march', 'marching-in-place'], 'p' => 1],
                ['pick' => ['mini-squat'], 'p' => 2],
                ['pick' => ['lateral-shuffle'], 'p' => 3],
                ['pick' => ['brisk-walking', 'easy-jog'], 'p' => 1],
            ],
        ],
    ],
];
