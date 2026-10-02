@props(['title' => null, 'page' => 'default', 'description' => null])
@php
    $nav = [
        ['route' => 'home', 'match' => 'home', 'icon' => 'home', 'label' => 'Home'],
        ['route' => 'warmup.builder', 'match' => 'warmup.*', 'icon' => 'play', 'label' => 'Warm-Up'],
        ['route' => 'timer', 'match' => 'timer', 'icon' => 'timer', 'label' => 'Timer'],
        ['route' => 'exercises.index', 'match' => 'exercises.*', 'icon' => 'book', 'label' => 'Library'],
        ['route' => 'progress', 'match' => 'progress', 'icon' => 'chart', 'label' => 'Progress'],
    ];
    $more = [
        ['route' => 'safety', 'match' => 'safety', 'icon' => 'shield', 'label' => 'Safety & Guidelines'],
        ['route' => 'sources', 'match' => 'sources', 'icon' => 'list', 'label' => 'Sources & Evidence'],
        ['route' => 'settings', 'match' => 'settings', 'icon' => 'settings', 'label' => 'Settings'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'Muscle-specific warm-ups and workouts for your training split — Push, Pull, Legs and Cardio & Core — with looping animated demonstrations.' }}">
    <meta name="theme-color" content="#0a0e13">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ReadyUp">
    <title>{{ $title ? $title.' · ReadyUp' : 'ReadyUp — Warm-Up & Movement Prep' }}</title>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('icons/icon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('icons/favicon-32.png') }}" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <script>
        {{-- Apply the saved theme before first paint to avoid a flash. --}}
        (function () {
            try {
                var s = JSON.parse(localStorage.getItem('readyup.settings') || '{}');
                var t = s.theme || 'dark';
                if (t === 'system') t = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="{{ $page }}">
    @include('partials.icons')

    <a href="#main" class="sr-only">Skip to content</a>

    <div class="app">
        <header class="topbar">
            <a href="{{ route('home') }}" class="brand" aria-label="ReadyUp home">
                <span class="brand-mark"><x-icon name="zap" fill /></span>
                <span>Ready<b>Up</b></span>
            </a>
            <div class="topbar-actions">
                <a href="{{ route('safety') }}" class="icon-btn" aria-label="Safety & Guidelines" title="Safety & Guidelines" @if (request()->routeIs('safety')) aria-current="page" @endif><x-icon name="shield" /></a>
                <a href="{{ route('settings') }}" class="icon-btn" aria-label="Settings" title="Settings" @if (request()->routeIs('settings')) aria-current="page" @endif><x-icon name="settings" /></a>
            </div>
        </header>

        <aside class="sidebar" aria-label="Main navigation">
            <nav>
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" /> {{ $item['label'] }}
                    </a>
                @endforeach
                <div class="sidebar-label">More</div>
                @foreach ($more as $item)
                    <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" /> {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="main" id="main">
            {{ $slot }}

            <footer class="site-footer">
                <nav aria-label="Secondary">
                    <a href="{{ route('safety') }}">Safety & Guidelines</a>
                    <a href="{{ route('sources') }}">Sources & Evidence</a>
                    <a href="{{ route('settings') }}">Settings</a>
                </nav>
                <p class="disclaimer">ReadyUp is an exercise, warm-up and workout guide, not a medical device or a substitute for individualised medical or physiotherapy advice. Warm up before training, use good technique, and stop if you experience sharp pain, dizziness or other concerning symptoms.</p>
            </footer>
        </main>
    </div>

    <nav class="bottom-nav" aria-label="Main navigation">
        @foreach ($nav as $item)
            <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                <x-icon :name="$item['icon']" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div id="toast-host" class="toast-host" aria-live="polite" aria-atomic="true"></div>

    <template id="animation-modal-template">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="animation-modal-title">
            <div class="modal-panel">
                <div class="modal-head">
                    <h2 id="animation-modal-title" data-slot="title"></h2>
                    <button type="button" class="icon-btn" data-close aria-label="Close animation"><x-icon name="x" /></button>
                </div>
                <div class="modal-body stack">
                    <div class="anim-stage" data-slot="stage"></div>
                    <p class="anim-ar" dir="rtl" lang="ar" data-slot="ar"></p>
                    <p class="muted small" data-slot="reps"></p>
                </div>
            </div>
        </div>
    </template>

    {{ $scripts ?? '' }}
</body>
</html>
