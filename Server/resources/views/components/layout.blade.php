<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sunburst')</title>


    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus+jakarta+sans:300,400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>

    <div class="dashboard-container">

        <aside class="icon-strip">
            <div class="logo-container">
                <img src="{{ asset('images/SunburstLogo.png') }}" width="60" height="60" alt="Logo">
            </div>

            <nav class="icon-nav">
                <a href="/api/members" class="icon-link">
                    <x-icons.house />
                </a>
                <a href="#" class="icon-link">
                    <x-icons.chat />
                </a>
                <a href="#" class="icon-link active">
                    <x-icons.grid />
                </a>
                <a href="#" class="icon-link">
                    <x-icons.heart />
                </a>
            </nav>

            <div class="user-profile">
                <x-icons.user stroke="black"/>
            </div>
        </aside>

        <aside class="nav-sidebar">
            <div class="sidebar-section">
                <div class="section-header">
                    <span class="section-title">Projects</span>
                </div>
                <div class="project-list">
                    <a href="#" class="project-item">
                        <span class="dot dot-blue"></span> Campaigns
                    </a>
                    <a href="#" class="project-item active">
                        <span class="dot dot-red"></span> Publications
                    </a>
                    <a href="#" class="project-item">
                        <span class="dot dot-green"></span> Development
                    </a>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-header">
                    <span class="section-title">Members</span>
                    <button class="add-btn">+</button>
                </div>
                <div class="member-list">
                    <div class="member-item">
                        <x-icons.user />
                        <span>Tin Phan</span>
                    </div>
                    <div class="member-item">
                        <x-icons.user />
                        <span>Gia Bao</span>
                    </div>
                    <div class="member-item">
                        <x-icons.user />
                        <span>Tuan Tran</span>
                    </div>
                </div>
            </div>

            <div class="promo-card">
                <span class="promo-tag">Unobvious Tips</span>
                <h4 class="promo-title">DEO BIET NEN LAM GI O DAY</h4>
                <p class="promo-meta">3 min read</p>
                <a href="#" class="promo-btn">
                    Read post <span class="arrow">→</span>
                </a>
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
                @yield('content')
            </main>
        </div>

    </div>

</body>
</html>
