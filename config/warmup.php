<?php

/*
|--------------------------------------------------------------------------
| Warm-up engine configuration
|--------------------------------------------------------------------------
|
| Templates describe WHICH exercises may fill each part of a routine and in
| what order. The RoutineBuilder decides WHICH candidate is used (level,
| intensity, impact, equipment and readiness rules) and HOW LONG each item
| runs so the routine fits the chosen time.
|
| Slot format:
|   'pick'   => candidate slugs in order of preference
|   'levels' => [level => [slugs]]  optional override of 'pick' for a level
|   'p'      => priority 1–4 (1 = always included, 4 = only when time allows)
|   'keep'   => true: never trimmed from very short routines (sport-specific finishers)
|
| Items inside a stage keep the order they are listed in — this is what
| creates the progression (e.g. walk → march → skip → jog → build-ups).
*/

return [

    'durations' => [5, 10, 15, 20],

    // Seconds between exercises in Workout Mode ("get ready" countdown).
    // Transitions are counted inside the time budget.
    'transition_seconds' => 5,

    // Seconds per stage (heat, mobility, activation, dynamic) for each duration.
    'stage_budgets' => [
        5 => ['heat' => 60, 'mobility' => 90, 'activation' => 90, 'dynamic' => 60],
        10 => ['heat' => 120, 'mobility' => 150, 'activation' => 180, 'dynamic' => 150],
        15 => ['heat' => 150, 'mobility' => 240, 'activation' => 270, 'dynamic' => 240],
        20 => ['heat' => 180, 'mobility' => 300, 'activation' => 330, 'dynamic' => 390],
    ],

    // Share of the dynamic-stage budget moved to mobility/activation.
    'budget_shift' => [
        'intensity' => ['light' => 0.20, 'moderate' => 0.0, 'high' => -0.15],
        'readiness' => ['ready' => 0.0, 'unchecked' => 0.0, 'modify' => 0.25, 'caution' => 0.50],
    ],

    'equipment' => [
        'mini-band' => 'Mini band (loop)',
        'long-band' => 'Long resistance band',
        'step' => 'Step / box / stairs',
        'bike' => 'Stationary bike',
        'ball' => 'Football',
    ],

    'default_equipment' => ['mini-band', 'step'],

    'quick' => [
        5 => ['title' => '5-Min Express', 'activity' => 'general', 'intensity' => 'moderate'],
        10 => ['title' => '10-Min Standard', 'activity' => 'general', 'intensity' => 'moderate'],
        15 => ['title' => '15-Min Complete', 'activity' => 'general', 'intensity' => 'moderate'],
    ],

    'templates' => [

        'gym' => [
            'heat' => [
                ['pick' => ['easy-cycling', 'brisk-walking', 'marching-in-place'], 'p' => 1],
                ['pick' => ['marching-in-place'], 'p' => 3],
            ],
            'mobility' => [
                ['pick' => ['ankle-rocks'], 'p' => 1],
                ['pick' => ['standing-hip-cars'], 'p' => 1],
                ['pick' => ['hip-90-90', 'figure-4-rocks'], 'p' => 2],
                ['pick' => ['hip-flexor-rock'], 'p' => 3],
                ['pick' => ['hamstring-scoops', 'leg-swings'], 'p' => 3],
                ['pick' => ['open-book'], 'p' => 2],
                ['pick' => ['arm-circles'], 'p' => 2],
                ['pick' => ['adductor-rockback'], 'p' => 4],
            ],
            'activation' => [
                ['pick' => ['banded-glute-bridge', 'glute-bridge'], 'p' => 1],
                ['pick' => ['clamshell', 'side-lying-hip-abduction'], 'p' => 2],
                ['pick' => ['dead-bug', 'bird-dog'], 'p' => 2],
                ['pick' => ['terminal-knee-extension', 'calf-raises'], 'p' => 3],
                ['pick' => ['lateral-band-walk', 'monster-walk', 'side-lying-hip-abduction'], 'p' => 3],
                ['pick' => ['bird-dog', 'forearm-plank'], 'p' => 4],
                ['pick' => ['single-leg-balance'], 'p' => 4],
            ],
            'dynamic' => [
                ['pick' => ['mini-squat', 'sit-to-stand'], 'levels' => [1 => ['sit-to-stand', 'mini-squat']], 'p' => 1],
                ['pick' => ['hip-hinge'], 'p' => 1],
                ['pick' => ['step-up', 'reverse-lunge'], 'levels' => [3 => ['reverse-lunge', 'step-up']], 'p' => 3],
                ['pick' => ['single-leg-rdl'], 'p' => 4],
                ['pick' => ['ramp-up-sets'], 'p' => 1, 'keep' => true],
            ],
        ],

        'running' => [
            // Running & football get a larger dynamic stage for gradual impact exposure.
            'budgets' => [
                5 => ['heat' => 60, 'mobility' => 75, 'activation' => 75, 'dynamic' => 90],
                10 => ['heat' => 90, 'mobility' => 135, 'activation' => 150, 'dynamic' => 225],
                15 => ['heat' => 120, 'mobility' => 210, 'activation' => 240, 'dynamic' => 330],
                20 => ['heat' => 150, 'mobility' => 270, 'activation' => 300, 'dynamic' => 480],
            ],
            'heat' => [
                ['pick' => ['brisk-walking', 'marching-in-place'], 'p' => 1],
            ],
            'mobility' => [
                ['pick' => ['ankle-rocks'], 'p' => 1],
                ['pick' => ['leg-swings'], 'p' => 1],
                ['pick' => ['standing-hip-cars'], 'p' => 2],
                ['pick' => ['hamstring-scoops'], 'p' => 2],
                ['pick' => ['hip-flexor-rock'], 'p' => 3],
                ['pick' => ['hip-90-90', 'figure-4-rocks'], 'p' => 3],
                ['pick' => ['adductor-rockback'], 'p' => 4],
                ['pick' => ['arm-circles'], 'p' => 4],
            ],
            'activation' => [
                ['pick' => ['calf-raises'], 'p' => 1],
                ['pick' => ['glute-bridge', 'banded-glute-bridge'], 'levels' => [2 => ['banded-glute-bridge', 'glute-bridge'], 3 => ['banded-glute-bridge', 'glute-bridge']], 'p' => 1],
                ['pick' => ['lateral-band-walk', 'side-lying-hip-abduction'], 'p' => 2],
                ['pick' => ['single-leg-balance'], 'p' => 2],
                ['pick' => ['mini-squat', 'sit-to-stand'], 'p' => 3],
                ['pick' => ['single-leg-rdl', 'clamshell'], 'levels' => [1 => ['clamshell']], 'p' => 4],
                ['pick' => ['side-plank', 'dead-bug'], 'p' => 4],
            ],
            'dynamic' => [
                ['pick' => ['a-march', 'marching-in-place'], 'p' => 1],
                ['pick' => ['a-skip'], 'p' => 2],
                ['pick' => ['easy-jog', 'brisk-walking', 'marching-in-place'], 'p' => 1],
                ['pick' => ['build-up-runs'], 'p' => 2],
            ],
        ],

        'football' => [
            'budgets' => [
                5 => ['heat' => 60, 'mobility' => 75, 'activation' => 75, 'dynamic' => 90],
                10 => ['heat' => 90, 'mobility' => 135, 'activation' => 150, 'dynamic' => 225],
                15 => ['heat' => 120, 'mobility' => 210, 'activation' => 240, 'dynamic' => 330],
                20 => ['heat' => 150, 'mobility' => 270, 'activation' => 300, 'dynamic' => 480],
            ],
            'heat' => [
                ['pick' => ['brisk-walking', 'marching-in-place'], 'p' => 1],
                ['pick' => ['marching-in-place'], 'p' => 2],
            ],
            'mobility' => [
                ['pick' => ['ankle-rocks'], 'p' => 1],
                ['pick' => ['standing-hip-cars'], 'p' => 1],
                ['pick' => ['leg-swings'], 'p' => 1],
                ['pick' => ['hip-90-90', 'figure-4-rocks'], 'p' => 2],
                ['pick' => ['hamstring-scoops'], 'p' => 2],
                ['pick' => ['adductor-rockback'], 'p' => 3],
                ['pick' => ['hip-flexor-rock'], 'p' => 3],
                ['pick' => ['open-book', 'arm-circles'], 'p' => 4],
            ],
            'activation' => [
                ['pick' => ['banded-glute-bridge', 'glute-bridge'], 'p' => 1],
                ['pick' => ['lateral-band-walk', 'side-lying-hip-abduction'], 'p' => 1],
                ['pick' => ['calf-raises'], 'p' => 2],
                ['pick' => ['single-leg-balance'], 'p' => 2],
                ['pick' => ['monster-walk', 'clamshell'], 'p' => 3],
                ['pick' => ['side-plank', 'dead-bug'], 'p' => 3],
                ['pick' => ['mini-squat', 'sit-to-stand'], 'p' => 4],
            ],
            'dynamic' => [
                ['pick' => ['easy-jog', 'a-march'], 'p' => 1],
                ['pick' => ['lateral-shuffle'], 'p' => 1],
                ['pick' => ['build-up-runs'], 'p' => 2],
                ['pick' => ['change-of-direction'], 'p' => 2],
                ['pick' => ['football-ball-work'], 'p' => 1, 'keep' => true],
            ],
        ],

        'general' => [
            'heat' => [
                ['pick' => ['marching-in-place', 'brisk-walking'], 'p' => 1],
            ],
            'mobility' => [
                ['pick' => ['standing-hip-cars'], 'p' => 1],
                ['pick' => ['ankle-rocks'], 'p' => 1],
                ['pick' => ['arm-circles'], 'p' => 2],
                ['pick' => ['open-book'], 'p' => 2],
                ['pick' => ['leg-swings', 'hamstring-scoops'], 'p' => 2],
                ['pick' => ['hip-90-90', 'figure-4-rocks'], 'p' => 3],
                ['pick' => ['hip-flexor-rock'], 'p' => 4],
            ],
            'activation' => [
                ['pick' => ['glute-bridge', 'banded-glute-bridge'], 'p' => 1],
                ['pick' => ['sit-to-stand', 'mini-squat'], 'levels' => [2 => ['mini-squat', 'sit-to-stand'], 3 => ['mini-squat', 'sit-to-stand']], 'p' => 1],
                ['pick' => ['bird-dog', 'dead-bug'], 'p' => 2],
                ['pick' => ['clamshell', 'side-lying-hip-abduction'], 'p' => 2],
                ['pick' => ['calf-raises'], 'p' => 3],
                ['pick' => ['single-leg-balance'], 'p' => 3],
                ['pick' => ['forearm-plank'], 'p' => 4],
            ],
            'dynamic' => [
                ['pick' => ['a-march', 'marching-in-place'], 'p' => 1],
                ['pick' => ['step-up'], 'p' => 2],
                ['pick' => ['lateral-shuffle'], 'p' => 3],
                ['pick' => ['brisk-walking', 'easy-jog'], 'levels' => [3 => ['easy-jog', 'brisk-walking']], 'p' => 1],
            ],
        ],

        /*
         | Focus programmes used by the "Knee Preparation" and
         | "Hip & Glute Preparation" sections.
         */
        'knee' => [
            'budgets' => [
                5 => ['heat' => 45, 'mobility' => 60, 'activation' => 165, 'dynamic' => 30],
                10 => ['heat' => 90, 'mobility' => 120, 'activation' => 330, 'dynamic' => 60],
            ],
            'heat' => [
                ['pick' => ['easy-cycling', 'marching-in-place'], 'p' => 1],
            ],
            'mobility' => [
                ['pick' => ['ankle-rocks'], 'p' => 1],
                ['pick' => ['hamstring-scoops'], 'p' => 2],
                ['pick' => ['leg-swings'], 'p' => 3],
            ],
            'activation' => [
                ['pick' => ['calf-raises'], 'p' => 1],
                ['pick' => ['sit-to-stand', 'mini-squat'], 'levels' => [2 => ['mini-squat', 'sit-to-stand'], 3 => ['mini-squat', 'sit-to-stand']], 'p' => 1],
                ['pick' => ['terminal-knee-extension', 'mini-squat'], 'p' => 2],
                ['pick' => ['step-up'], 'p' => 2],
                ['pick' => ['single-leg-balance'], 'p' => 2],
                ['pick' => ['banded-glute-bridge', 'glute-bridge'], 'p' => 3],
                ['pick' => ['lateral-band-walk', 'side-lying-hip-abduction'], 'p' => 3],
            ],
            'dynamic' => [
                ['pick' => ['a-march', 'marching-in-place'], 'p' => 2],
            ],
        ],

        'hip' => [
            'budgets' => [
                5 => ['heat' => 45, 'mobility' => 105, 'activation' => 120, 'dynamic' => 30],
                10 => ['heat' => 90, 'mobility' => 210, 'activation' => 240, 'dynamic' => 60],
            ],
            'heat' => [
                ['pick' => ['marching-in-place', 'brisk-walking'], 'p' => 1],
            ],
            'mobility' => [
                ['pick' => ['standing-hip-cars'], 'p' => 1],
                ['pick' => ['hip-90-90'], 'p' => 1],
                ['pick' => ['figure-4-rocks'], 'p' => 2],
                ['pick' => ['leg-swings'], 'p' => 2],
                ['pick' => ['adductor-rockback'], 'p' => 3],
                ['pick' => ['hip-flexor-rock'], 'p' => 3],
            ],
            'activation' => [
                ['pick' => ['glute-bridge', 'banded-glute-bridge'], 'p' => 1],
                ['pick' => ['clamshell'], 'p' => 1],
                ['pick' => ['side-lying-hip-abduction'], 'p' => 2],
                ['pick' => ['lateral-band-walk'], 'p' => 2],
                ['pick' => ['monster-walk'], 'p' => 3],
                ['pick' => ['side-plank', 'bird-dog'], 'p' => 3],
                ['pick' => ['single-leg-rdl', 'single-leg-balance'], 'p' => 4],
            ],
            'dynamic' => [
                ['pick' => ['a-march', 'marching-in-place'], 'p' => 2],
            ],
        ],
    ],

    'focus' => [
        'knee' => [
            'title' => 'Knee Preparation',
            'emoji' => '🦵',
            'durations' => [5, 10],
        ],
        'hip' => [
            'title' => 'Hip & Glute Preparation',
            'emoji' => '🧘',
            'durations' => [5, 10],
        ],
    ],
];
