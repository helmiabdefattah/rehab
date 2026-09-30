<?php

/*
|--------------------------------------------------------------------------
| Curated instructional videos
|--------------------------------------------------------------------------
|
| Every URL below was taken verbatim from web-search results (no IDs were
| constructed or guessed) and cross-checked where possible:
|
|   verification = 'confirmed'  the same URL + matching title was returned by a
|                               second, independent search (video id or keyword)
|   verification = 'single'     seen in one search result only — please review
|
| Exercises that are NOT listed here have no curated video yet. The app then
| shows a clearly-labelled "Find a demonstration" button that opens a YouTube
| search for the exercise (a search link, never an invented video URL).
|
| Re-check every link from a machine with internet access with:
|
|     php artisan videos:verify
|
| and set / replace a video with:
|
|     php artisan videos:set {slug} {youtube-url} --title="…" --channel="…"
*/

return [
    'marching-in-place' => [
        'url' => 'https://www.youtube.com/watch?v=u1gmWFvEluM',
        'title' => 'Marching in Place Demonstrated by a Physical Therapist',
        'channel' => 'Margaret Martin, PT (MelioGuide)',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'ankle-rocks' => [
        'url' => 'https://www.youtube.com/watch?v=o-KRVtnrOVk',
        'title' => 'Knee to Wall Stretch Exercise | Level 1 | The Physios',
        'channel' => 'The Physios',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'standing-hip-cars' => [
        'url' => 'https://www.youtube.com/watch?v=m_l9DCL2zL0',
        'title' => 'Hip CARs - Standing (FRC) | Adam Wolf PT',
        'channel' => 'Adam Wolf PT',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'hip-90-90' => [
        'url' => 'https://www.youtube.com/watch?v=m51AZSXMvEA',
        'title' => '90 90 Hip Switch',
        'channel' => 'Active Life Professionals',
        'source_type' => 'other',
        'verification' => 'confirmed',
    ],
    'figure-4-rocks' => [
        'url' => 'https://www.youtube.com/watch?v=-g0nuyTHMrI',
        'title' => 'Piriformis Figure 4 Stretch - Ask Doctor Jo',
        'channel' => 'Ask Doctor Jo',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
        'note' => 'The video demonstrates the figure-4 position as a held stretch. In this app, ease in and out of it gently instead of holding a strong stretch.',
    ],
    'leg-swings' => [
        'url' => 'https://www.youtube.com/watch?v=naW8u72lOzI',
        'title' => '3. Leg Swings - Active Warm-Up - Fully Fit by Runner\'s World',
        'channel' => 'Runner\'s World',
        'source_type' => 'running-coach',
        'verification' => 'confirmed',
    ],
    'hamstring-scoops' => [
        'url' => 'https://www.youtube.com/watch?v=JTIhj2r31bo',
        'title' => 'How to Do Hamstring Scoops',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'confirmed',
    ],
    'adductor-rockback' => [
        'url' => 'https://www.youtube.com/watch?v=yF8o6I6aSZg',
        'title' => 'Adductor Quadruped Rockback - Hip Mobility Drill',
        'channel' => 'Mike Reinold',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'open-book' => [
        'url' => 'https://www.youtube.com/watch?v=peeW19ofFUg',
        'title' => 'Thoracic Rotation Open Book',
        'channel' => 'Elite Performance Institute',
        'source_type' => 'other',
        'verification' => 'confirmed',
    ],
    'arm-circles' => [
        'url' => 'https://www.youtube.com/watch?v=hne3nHGXPRM',
        'title' => 'Arm Circles (Exercise Library)',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'confirmed',
    ],
    'glute-bridge' => [
        'url' => 'https://www.youtube.com/watch?v=WtilA9IJX1c',
        'title' => 'Glute Bridges Exercise for Hips & Butt',
        'channel' => 'Release Physical Therapy',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'banded-glute-bridge' => [
        'url' => 'https://www.youtube.com/watch?v=GjLPEfu5PN0',
        'title' => 'Basic Banded Glute Bridge',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'single',
    ],
    'clamshell' => [
        'url' => 'https://www.youtube.com/watch?v=k2-Wq7WCS0I',
        'title' => 'The Clamshell: A "go to" Exercise for Treating Foot, Hip, and Knee Pain',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'confirmed',
    ],
    'sit-to-stand' => [
        'url' => 'https://www.youtube.com/watch?v=qveKmiXEkIQ',
        'title' => 'How to Do a Sit to Stand: A Guide from Physical Therapists',
        'channel' => 'Hinge Health',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'mini-squat' => [
        'url' => 'https://www.youtube.com/watch?v=X8XutSsocx4',
        'title' => 'Standing Mini Squat',
        'channel' => 'VNA Health Group',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'terminal-knee-extension' => [
        'url' => 'https://www.youtube.com/watch?v=7xG3MeoLjC0',
        'title' => 'How to perform the banded Terminal Knee Extension (TKE) exercise',
        'channel' => null,
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'step-up' => [
        'url' => 'https://www.youtube.com/watch?v=1hiWQ7pehjQ',
        'title' => 'Wellness Wednesday: Build your stair climbing power with step-ups',
        'channel' => 'Mayo Clinic',
        'source_type' => 'medical-organization',
        'verification' => 'confirmed',
    ],
    'reverse-lunge' => [
        'url' => 'https://www.youtube.com/watch?v=w7pyyqLorJ4',
        'title' => 'Do Reverse Lunges Like a Pro: A Simple Step-by-Step Guide',
        'channel' => 'Hinge Health',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'hip-hinge' => [
        'url' => 'https://www.youtube.com/watch?v=9Qwob4CWalw',
        'title' => 'Hip - Hip Hinge with Dowel',
        'channel' => 'Physical Therapy First',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'dead-bug' => [
        'url' => 'https://www.youtube.com/watch?v=GbSC02oU3To',
        'title' => 'How to Do a Dead Bug: A Guide from Physical Therapists',
        'channel' => 'Hinge Health',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'bird-dog' => [
        'url' => 'https://www.youtube.com/watch?v=xEDnlOxeJH4',
        'title' => 'How to Do the Bird Dog Exercise: A Guide from Physical Therapists',
        'channel' => 'Hinge Health',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'forearm-plank' => [
        'url' => 'https://www.youtube.com/watch?v=3QZlgJ40LfU',
        'title' => 'Demonstration of a Forearm Plank',
        'channel' => 'HSS (Hospital for Special Surgery)',
        'source_type' => 'medical-organization',
        'verification' => 'confirmed',
    ],
    'side-plank' => [
        'url' => 'https://www.youtube.com/watch?v=0Rl5ZQwmS-o',
        'title' => 'Side Plank Exercise for Core & Low Back',
        'channel' => 'Release Physical Therapy',
        'source_type' => 'physiotherapy',
        'verification' => 'single',
    ],
    'single-leg-balance' => [
        'url' => 'https://www.youtube.com/watch?v=Dtgh2_LFkBQ',
        'title' => 'Single Leg Balance - Ask Doctor Jo',
        'channel' => 'Ask Doctor Jo',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'single-leg-rdl' => [
        'url' => 'https://www.youtube.com/watch?v=grMi99GhV1k',
        'title' => 'Learning the Single Leg RDL',
        'channel' => '[P]rehab (The Prehab Guys)',
        'source_type' => 'physiotherapy',
        'verification' => 'confirmed',
    ],
    'a-march' => [
        'url' => 'https://www.youtube.com/watch?v=c8il_EjiBWQ',
        'title' => 'Running Drill - A\'s - Running Drills - A March & A Skip',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'confirmed',
    ],
    'a-skip' => [
        'url' => 'https://www.youtube.com/watch?v=GQg9L28bi1g',
        'title' => 'A Skip Running Drill',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'confirmed',
    ],
    'easy-jog' => [
        'url' => 'https://www.youtube.com/watch?v=wue6U_nqZP0',
        'title' => 'Master Easy Running: How to Find "Easy Pace"',
        'channel' => 'Jason Fitzgerald (Strength Running)',
        'source_type' => 'running-coach',
        'verification' => 'single',
    ],
    'build-up-runs' => [
        'url' => 'https://www.youtube.com/watch?v=1i2ZPpXtuOk',
        'title' => 'How To Run Strides And How They Make You Faster',
        'channel' => null,
        'source_type' => 'running-coach',
        'verification' => 'single',
    ],
    'lateral-shuffle' => [
        'url' => 'https://www.youtube.com/watch?v=QFSdjLw93sA',
        'title' => 'Lateral Shuffle Ladder Drill',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'single',
        'note' => 'The video uses an agility ladder — the same side-shuffle footwork works on open ground without a ladder.',
    ],
    'change-of-direction' => [
        'url' => 'https://www.youtube.com/watch?v=aj0MgislRto',
        'title' => 'FIFA 11+ with Ontario Soccer: 15 - Running (Plant and Cut)',
        'channel' => 'Ontario Soccer',
        'source_type' => 'football-organization',
        'verification' => 'confirmed',
        'note' => 'FIFA 11+ performs this at higher speed. In this app, start at an easy jog with gentle 45° turns and only build speed gradually.',
    ],
    'football-ball-work' => [
        'url' => 'https://www.youtube.com/watch?v=V-lINok1UqA',
        'title' => 'Pass & Move Circle',
        'channel' => null,
        'source_type' => 'other',
        'verification' => 'confirmed',
        'note' => 'Shown as a group drill — with one partner or a wall, use the same short pass-and-move pattern.',
    ],
    'ramp-up-sets' => [
        'url' => 'https://www.youtube.com/watch?v=MncQw-H3MPU',
        'title' => 'How to PROPERLY Warm Up Before Weights',
        'channel' => 'Jeremy Ethier (Built With Science)',
        'source_type' => 'fitness-education',
        'verification' => 'single',
    ],
];
