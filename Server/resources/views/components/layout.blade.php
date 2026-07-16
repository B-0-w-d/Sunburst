<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sunburst' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus+jakarta+sans:300,400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])
    @stack('styles')
</head>

<body>

    <div class="dashboard-container">
        <aside class="icon-strip">
            <img src="{{ asset('images/SunburstLogo.png') }}" width="60" height="60" alt="Logo">

            <nav class="icon-nav">
                <a href="/" class="icon-link">
                    <x-icons.house />
                </a>
                <a href="#" class="icon-link">
                    <x-icons.chat />
                </a>
                <a href="/members" class="icon-link active">
                    <x-icons.grid />
                </a>
                <a href="#" class="icon-link">
                    <x-icons.heart />
                </a>
            </nav>

            <div class="user-profile">
                <x-icons.user stroke="black" />
            </div>
        </aside>

        <div class="main-canvas">
            <header class="canvas-header">
                <div class="header-left">
                    <h2 class="page-title">Dashboard Console</h2>
                    <div class="filter-tabs">
                        <button class="tab-btn active">Server</button>
                        <button class="tab-btn">Overview</button>
                    </div>
                </div>
                <div class="header-right">
                    <div class="status-indicator">
                        <span class="pulse-dot"></span> API Active
                    </div>
                </div>
            </header>

            <main class="canvas-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>