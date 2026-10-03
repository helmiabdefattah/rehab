# ReadyUp — Warm-Up & Workout by Training Split (Laravel)

A mobile-first web app organised around your **training split**: **Push**, **Pull**, **Legs** and **Cardio & Core**. Pick what you are training today and you get a **warm-up built for exactly those muscles** — so you perform at your best and cut the risk of injury — followed by **the workout itself**. Every exercise ships with a built-in **looping animated demonstration** (inline SVG, no videos), so the app works offline.

> **Disclaimer** — ReadyUp is an exercise, warm-up and workout guide, not a medical device or a substitute for individualised medical or physiotherapy advice. The exercises and set/rep ranges are general suggestions — adapt them to your own program, equipment and experience. Warm up before training, use good technique, and stop if you experience sharp pain, dizziness or other concerning symptoms.

---

## Features

| Area | What it does |
|---|---|
| **Home** | Split tiles (💪 Push, 🪢 Pull, 🦵 Legs, 🫀 Cardio & Core), ⚡ Quick full-body warm-up (5 / 10 / 15 min, starts immediately), Build My Warm-Up, library, training timer and a progress snapshot |
| **Split hub** (`/train/{split}`) | Two parts per split: **1. Warm-up** (a split-specific routine) and **2. Workout** (the training exercises with sets × reps × rest and a rest-timer link) |
| **Build My Warm-Up** | 3-step wizard: split → intensity (light/moderate/high) + time (5/10/15/20 min) + level (1–3) + equipment → generated routine preview |
| **Routine engine** | Four stages — *General heat → Dynamic mobility → Activation & stability → Movement rehearsal*. The engine picks exercises by suitability (level, intensity, impact, equipment) and fits the routine exactly to the chosen minutes, including transitions |
| **Animated demonstrations** | Every exercise has a looping stick-figure SVG keyed to its movement pattern (press, pull, squat, hinge, lunge, bridge, plank, cardio…). No external video, works offline, themed for light/dark |
| **Workout Mode** | Full-screen, minimal text: big countdown, exercise name, inline animation, View-animation modal, progress bars, next-exercise preview, Pause / Skip / Previous / Restart, "switch sides" cue, 3-2-1 beeps, vibration, optional voice cues, screen wake lock, keyboard shortcuts, resume after reload |
| **Post-session feedback** | 😊 Good / 😐 Okay / 😟 Uncomfortable, pain yes/no → area, notes. Records only; no diagnosis |
| **Training Timer** | Rest timer between sets (presets, +15 s, auto set counter), interval timer (work/rest/rounds, presets such as 20/10 × 8, EMOM), stopwatch with laps, countdown |
| **Exercise Library** | 72 warm-up and workout exercises with English + Arabic names, purpose, why it's included, steps, common mistakes, safety notes, progression / regression, tags and an animated demo. Filter by **part** (warm-up / workout) and **training split**, instant search |
| **My Progress** | Sessions completed, total sessions, exercises completed, weekly chart, split breakdown, how you felt, session history |
| **Safety & Guidelines** | Why to warm up per split, stop signs, the four warm-up stages, intensity levels |
| **Sources & Evidence** | References grouped by topic |
| **Settings** | Level, default equipment, get-ready seconds, sound, 3-2-1 beeps, voice cues, vibration, keep-awake, dark/light/system theme, export/import/delete data |
| **PWA / full offline** | Installable (Add to Home Screen). After one online load it works **fully offline**: warm-ups are generated in the browser (the routine engine is ported to JS and the exercise data is baked into the bundle), and the service worker precaches every key page (home, builder, the four split hubs, quick warm-ups, timer, library, progress, settings). Needs a secure context — HTTPS, or `localhost`/`127.0.0.1` for dev |

## Tech stack

- **Laravel 13** (PHP 8.3+) with **MySQL** (tested on MySQL 8; SQLite or PostgreSQL also work by changing `DB_CONNECTION`)
- **Blade** views and components, **vanilla ES modules** bundled by **Vite**, hand-written CSS (no UI framework)
- No login: **progress and settings are stored in the browser (localStorage)**, so health-related notes stay on the device. Export/import is available in Settings.

```
app/
  Enums/                    Activity (training split), Intensity, Level, Stage, Impact
  Services/Warmup/          RoutineBuilder (+ request/result value objects)
  Http/Controllers/         Pages + WorkoutController (/train/{split}) + Api/RoutineController (POST /api/routines)
config/warmup.php           Stage budgets, per-split warm-up templates, workout plans, equipment
database/data/              exercises.php (72 warm-up + workout exercises), sources.php
resources/js/components/    exercise-animation.js (looping SVG movement demos)
resources/views/            Blade pages and components (exercise card, layout, workout templates, split hub)
resources/js/               lib/ (timers, cues, storage), workout/ (player, finish), pages/
tests/                      Routine engine, content, split enum, pages, API
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
php artisan db:seed --class=ExerciseSeeder   # re-load exercises after editing database/data/*.php
php artisan warmup:export-js                 # regenerate the offline JS data bundle after editing exercises/config
php artisan test                             # full suite (uses in-memory SQLite by default)
vendor/bin/pint                              # code style
```

Tests run on in-memory SQLite, so they never touch your real data. If your PHP has no `pdo_sqlite`, run them against a separate MySQL database instead. In PowerShell:

```powershell
$env:DB_CONNECTION="mysql"; $env:DB_DATABASE="rehab_test"; php artisan test
```

(Create the empty `rehab_test` database first; the tests reset it on every run.)

## How the routine engine works

1. **Templates** (`config/warmup.php`) list, per split and stage, the candidate warm-up exercises for each slot in order of preference, with a priority (1 = essential … 4 = only if time allows). The order inside a stage creates the progression. For example, Cardio & Core goes A-march → A-skip → easy jog → build-ups, and Push rehearses shoulder prep → activation → ramp-up sets.
2. **Selection** picks the first candidate that suits today's level, intensity, impact cap and available equipment. Substitutions are explained under "Adjustments made".
3. **Timing** admits slots by priority into each stage's time budget, then scales durations within each exercise's min/max so the whole routine, transitions included, matches the chosen minutes exactly. This is tested across every split / duration / intensity / level / equipment combination.

The **workout** for each split is a simple ordered list of training exercises (`config/warmup.php` → `workouts`), each with a set/rep/rest prescription.

**Levels** — 1 Re-entry (low impact, controlled), 2 Conditioning (moderate dynamic, greater range), 3 Performance Preparation (controlled acceleration, lateral movement). Level-2+ drills (A-skip, build-up runs) never appear at Level 1. **The user always decides**; nothing is automatic.

## Exercise animations

Instead of external videos, every exercise renders a **looping SVG stick-figure animation** keyed to its movement pattern (`animation` field on each exercise). `resources/js/components/exercise-animation.js` draws a standing rig driven by SMIL `<animateTransform>` for standing patterns (press, pull, squat, hinge, lunge, cardio, arm-circle, calf, twist, balance…) and small bespoke scenes for floor patterns (plank, bridge, dead-bug, crunch, side-lying). The figure is themed with CSS so it reads in both light and dark mode, loops forever, and works fully offline — no third-party links.

## Offline

The app is built to work **fully offline** after one online load (over HTTPS, or `localhost`/`127.0.0.1` in dev):

- **Warm-ups are generated client-side.** `resources/js/warmup/engine.js` is a JavaScript port of the PHP `RoutineBuilder`, and `resources/js/data/warmup-data.js` (regenerated with `php artisan warmup:export-js`) bakes the warm-up exercises and templates into the bundle — so both the custom *Build My Warm-Up* and the ⚡ quick warm-ups build with no server round-trip. (The `POST /api/routines` endpoint still exists, server-side, for tests and external use, but the UI no longer needs it.)
- **The service worker precaches** every key page (home, builder, the four `/train/{split}` hubs, quick warm-ups, timer, library, progress, settings, safety, sources) plus the built CSS/JS, so they open offline without having to be visited first.
- **Everything else is already client-side**: the animations (inline SVG), Workout Mode, the guided workout player, and the training timer all run in the browser.

## Privacy

Session history and settings live only in the browser's localStorage. Nothing is sent to the server for building a routine — it is all computed in the browser and never stored.

## Final quality-control checklist

**Content** — ✅ 72 warm-up + workout exercises · ✅ muscle-specific warm-up per split · ✅ full workout per split (6 exercises each) · ✅ Push / Pull / Legs / Cardio & Core
**Animations** — ✅ every exercise has a looping animated demo · ✅ no external video links · ✅ works offline · ✅ themed for light/dark
**Functionality** — ✅ timers · ✅ navigation · ✅ Workout Mode · ✅ progress tracking · ✅ routine builder · ✅ split hub (warm-up + workout) · ✅ mobile layout (no horizontal scroll)
**Safety** — ✅ no diagnosis · ✅ no "prevents injury" claims (tested) · ✅ warm-up-first guidance
