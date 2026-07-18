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
                {{-- Dynamically checks if current path is root '/' --}}
                <a href="/" class="icon-link {{ request()->is('/') ? 'active' : '' }}">
                    <x-icons.house />
                </a>
                <a href="#" class="icon-link">
                    <x-icons.chat />
                </a>
                {{-- Fixed matching state checking if route path contains 'members' --}}
                <a href="/members" class="icon-link {{ request()->is('members*') ? 'active' : '' }}">
                    <x-icons.grid />
                </a>
                <a href="#" class="icon-link">
                    <x-icons.heart />
                </a>
            </nav>

            {{-- Component Structure with CSS-driven hovering indicators --}}
            <div class="user-profile-container">
                <div class="user-profile">
                    @auth
                        {{ substr(auth()->user()->name, 0, 1) }}
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    @endauth
                </div>

                <!-- The Hover Dropdown Deck -->
                <div class="profile-dropdown">
                    @auth
                        <div class="dropdown-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-email">{{ auth()->user()->email }}</span>
                        </div>

                        <!-- Added Edit Profile Link -->
                        <button type="button"
                                onclick="openModal('editProfileModal')"
                                class="dropdown-item"
                                style="background: none; border: none; width: 100%; text-align: center; cursor: pointer;">
                            Edit Profile
                        </button>

                        <form action="/logout" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item">Log Out</button>
                        </form>
                    @endauth
                </div>
            </div>
        </aside>

        <div class="main-canvas">
            <header class="canvas-header">
                <div class="header-left">
                    <h2 class="page-title">Sunburst Dashboard</h2>
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

    <div id="editProfileModal" class="modal">
            @include('components.profile')
    </div>
    @stack('scripts')
</body>

</html>
