# ReadyUp — Warm-Up & Movement Preparation (Laravel)

A mobile-first web app that builds a **personalised, progressive warm-up** before the gym, running, football or general exercise. It is designed for an adult who has **completed rehabilitation** after a previous knee cartilage surgery and a previous piriformis / glute injury. It gives extra attention to knee preparation, glute activation, hip mobility and stability, and a gradual return to impact.

> **Medical disclaimer** — This application is an exercise and warm-up guide, not a medical device or substitute for individualized medical or physiotherapy advice. Because the user has a history of knee cartilage surgery and a previous Piriformis/Glute injury, exercise selection and progression should respect any restrictions previously provided by their healthcare professional. Stop if you experience significant pain, instability, locking, swelling, or other concerning symptoms and seek professional assessment.

---

## Features

| Area | What it does |
|---|---|
| **Home** | ⚡ Quick 10-min warm-up (starts immediately), ⚡ 5-min Express / 10-min Standard / 15-min Complete, activity tiles (🏋️ Gym, 🏃 Running, ⚽ Football, 🚶 General), Knee and Hip & Glute sections, training timer, progress snapshot |
| **Build My Warm-Up** | 4-step wizard: activity → intensity (light/moderate/high) + time (5/10/15/20 min) + level (1–3) + equipment → **daily readiness check** (7 questions + 1–10 score) → generated routine preview |
| **Routine engine** | Four stages — *General heat → Dynamic mobility → Activation & stability → Dynamic & sport preparation*. The engine picks exercises by suitability (level, intensity, impact, equipment, reported symptoms) and fits the routine exactly to the chosen minutes, including transitions |
| **Readiness logic** | Good readiness → normal routine. Stiffness only / 4–6 → more mobility and activation, moderate caps. Pain, swelling, instability, pain on walking/stairs, or ≤3 → caution message, light and low-impact only, knee- or hip-loading drills removed. Never diagnoses the user or labels them as injured |
| **Workout Mode** | Full-screen, minimal text: big countdown, exercise name, demonstration thumbnail, ▶ Watch Video, progress bars, next-exercise preview, Pause / Skip / Previous / Restart, "switch sides" cue, 3-2-1 beeps, vibration, optional voice cues, screen wake lock, keyboard shortcuts, resume after reload |
| **Post-session feedback** | 😊 Good / 😐 Okay / 😟 Uncomfortable, pain yes/no → Knee / Hip / Glute / Piriformis area / Ankle / Other, notes. Records only and suggests reducing intensity or seeking advice; no diagnosis |
| **Training Timer** | Rest timer between sets (presets, +15 s, auto set counter), interval timer (work/rest/rounds, presets such as 20/10 × 8, EMOM), stopwatch with laps, countdown |
| **Exercise Library** | 40 exercises with English + Arabic names, purpose, why it's included, steps, common mistakes, safety notes, progression / regression, tags, suitable activities and a video. Instant search and filters |
| **Knee Preparation / Hip & Glute Preparation** | Dedicated sections with guidance and 5- or 10-minute focused routines |
| **My Progress** | Warm-ups completed, total sessions, average readiness, exercises completed, weekly chart, activity breakdown, how you felt, pain reports, session history, and a **progression check** that never moves you up automatically |
| **Safety & Guidelines** | Previous-injury considerations, stop signs, readiness rules, levels, knee and hip guidance |
| **Sources & Evidence** | 20 references grouped by topic and a table of every exercise video with its verification status |
| **Settings** | Level, default equipment, get-ready seconds, sound, 3-2-1 beeps, voice cues, vibration, keep-awake, pause-on-video, dark/light/system theme, export/import/delete data |
| **PWA** | Installable (Add to Home Screen), offline fallback for key pages over HTTPS |

## Tech stack

- **Laravel 13** (PHP 8.3+) with **MySQL** (tested on MySQL 8; SQLite or PostgreSQL also work by changing `DB_CONNECTION`)
- **Blade** views and components, **vanilla ES modules** bundled by **Vite**, hand-written CSS (no UI framework)
- No login: **progress and settings are stored in the browser (localStorage)**, so health-related notes stay on the device. Export/import is available in Settings.

```
app/
  Enums/                    Activity, Intensity, Level, Stage, Impact, ReadinessStatus
  Services/Warmup/          ReadinessEvaluator, RoutineBuilder (+ request/result value objects)
  Http/Controllers/         Pages + Api/RoutineController (POST /api/routines)
  Console/Commands/         videos:verify, videos:set
config/warmup.php           Stage budgets, activity/focus templates, equipment
database/data/              exercises.php (40 exercises), videos.php (curated videos), sources.php
resources/views/            Blade pages and components (exercise card, layout, workout templates)
resources/js/               lib/ (timers, cues, storage), workout/ (player, finish), pages/
tests/                      Readiness rules, routine engine, content safety, pages, API, commands
```

## Getting started

Requirements: PHP 8.3+ (with `pdo_mysql`), Composer, Node 20+, and a MySQL server (Laragon, XAMPP, MySQL Installer…).

```bash
git clone https://github.com/helmiabdefattah/rehab.git
cd rehab
composer run setup      # install, .env, key, create DB + migrate + seed, npm install, build
php artisan serve       # http://localhost:8000
```

**Database:** the defaults in `.env.example` are `DB_DATABASE=rehab`, `DB_USERNAME=root` and an empty password — the usual Laragon/XAMPP setup. If your MySQL uses a password, run `copy .env.example .env` first, set `DB_USERNAME` / `DB_PASSWORD` in `.env`, then run `composer run setup`. The `rehab` database is created automatically if it doesn't exist.

On Windows (e.g. `D:\sites\rehab`), check that `extension=pdo_mysql` is enabled in `php.ini` (it is by default in Laragon and XAMPP). With Laragon/XAMPP you can point the site's document root at the `public/` folder instead of using `php artisan serve`.

`composer run dev` runs the server and Vite (hot reload) together.

**Use it on your phone:** run `php artisan serve --host=0.0.0.0 --port=8000` and open `http://<your-computer-ip>:8000` on the same Wi-Fi, then *Add to Home Screen*. Screen wake lock, the service worker and some audio features need **HTTPS** outside `localhost`, so deploy behind HTTPS for the full experience.

Useful commands:

```bash
php artisan db:seed --class=ExerciseSeeder   # re-load exercises/videos after editing database/data/*.php
php artisan test                             # 56 tests (uses in-memory SQLite by default)
vendor/bin/pint                              # code style
```

Tests run on in-memory SQLite, so they never touch your real data. If your PHP has no `pdo_sqlite`, run them against a separate MySQL database instead. In PowerShell:

```powershell
$env:DB_CONNECTION="mysql"; $env:DB_DATABASE="rehab_test"; php artisan test
```

(Create the empty `rehab_test` database first; the tests reset it on every run.)

## How the routine engine works

1. **Readiness** (`ReadinessEvaluator`) turns the answers into caps on level, intensity and impact, plus symptom groups (`knee`, `hip`) that remove specific exercises (for example step-ups, lunges and skipping for knee symptoms; 90/90 and figure-4 for hip or piriformis symptoms).
2. **Templates** (`config/warmup.php`) list, per activity and stage, the candidate exercises for each slot in order of preference, with a priority (1 = essential … 4 = only if time allows). The order inside a stage creates the progression. For example, running goes marching → A-skip → easy jog → build-ups, and football goes jog → lateral shuffle → build-ups → change of direction → ball work.
3. **Selection** picks the first candidate that suits today's level, intensity, impact cap, available equipment and readiness. Substitutions are explained under "Adjustments made".
4. **Timing** admits slots by priority into each stage's time budget, then scales durations within each exercise's min/max so the whole routine, transitions included, matches the chosen minutes exactly. This is tested across all 288 combinations.

**Levels** — 1 Re-entry (low impact, controlled), 2 Conditioning (moderate dynamic, greater range), 3 Performance Preparation (controlled acceleration, lateral movement, activity-specific). Level-2+ drills (A-skip, build-up runs, change of direction, reverse lunge, single-leg RDL) never appear at Level 1. The Progress page shows criteria (consistent sessions at the current level, no pain, felt Good/Okay, readiness ≥ 7). **The user always decides**; nothing is automatic.

## Exercise videos — status and how to finish curating

Every exercise card has a video button. URLs were taken **verbatim from web-search results** and never constructed. Where possible, a second independent search confirmed the same URL and title.

| Status | Count | Exercises |
|---|---|---|
| Cross-checked (URL + title seen in two searches) | 27 | all others |
| Seen in one search only (labelled "Please review") | 6 | banded glute bridge, side plank, easy jog, build-up runs, lateral shuffle, ramp-up sets |
| **No curated video yet** (button reads **"Find a Video"** and opens a YouTube search) | 7 | brisk walking, easy stationary cycling, half-kneeling hip flexor rock, side-lying hip abduction, mini-band lateral walk, monster walk, calf raises |

The build environment could not open YouTube directly, and its web-search quota ran out before the last 7 were found. Please:

```bash
php artisan videos:verify     # checks every link via YouTube oEmbed and prints the real title + channel
php artisan videos:set calf-raises "https://www.youtube.com/watch?v=XXXXXXXXXXX" \
    --title="…" --channel="…" --source=physiotherapy   # updates database/data/videos.php and the DB
```

Prefer short single-exercise videos from physiotherapists, sports-medicine organisations or S&C coaches.

## Privacy

Session history (including pain notes) and settings live only in the browser's localStorage. Nothing is sent to the server except the routine request (activity, time, intensity, level, equipment and readiness answers), which is computed and not stored.

## Final quality-control checklist

**Content** — ✅ 40 exercises · ✅ knee considerations · ✅ glute/piriformis considerations · ✅ dynamic warm-up prioritised · ✅ progressive loading · ✅ football, running and gym routines
**Videos** — ✅ every exercise has a working video button · ✅ no fabricated URLs · ⚠️ 7 exercises still need a curated video (search fallback shown) and 6 are single-search; run `videos:verify` from a connected machine
**Functionality** — ✅ timers · ✅ navigation · ✅ Workout Mode · ✅ progress tracking · ✅ routine builder · ✅ mobile layout (no horizontal scroll at 390 px) · ✅ all buttons wired (checked with an automated Chromium run)
**Safety** — ✅ no diagnosis · ✅ no "prevents injury" claims (tested) · ✅ no aggressive stretching · ✅ no sudden high-impact progression (tested) · ✅ symptom warning system
