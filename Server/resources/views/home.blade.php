{{-- resources/views/home.blade.php --}}
<x-layout title="Welcome to Sunburst">
    <div class="welcome-container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh; text-align: center; font-family: 'Inter', sans-serif; padding: 20px; background-color: #fcfcfd;">

        <!-- Welcome Hero Branding Block -->
        <h1 style="font-size: 2.75rem; color: #0f172a; margin-bottom: 16px; font-weight: 800; letter-spacing: -0.025em;">
            Welcome to Sunburst
        </h1>

        <p style="font-size: 1.15rem; color: #475569; max-width: 560px; line-height: 1.625; margin-bottom: 32px; font-weight: 400;">
            You have successfully cleared console authentication tiers. Select an environment hub below to manage the application roster channels.
        </p>

        <!-- Dynamic Redirection Actions Area -->
        <div style="display: flex; gap: 16px; justify-content: center; align-items: center;">
            <a href="/members" class="btn-save" style="background-color: #4f46e5; color: white; text-decoration: none; padding: 12px 28px; font-size: 15px; border-radius: 6px; font-weight: 600; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1); transition: background-color 0.2s;">
                Go to Members Roster Dashboard
            </a>

            {{-- Dev Mode Quick-Link Bypass: Rendered dynamically if the user has developer clearance context --}}
            @if(auth()->check() && strtolower(auth()->user()->role ?? '') === 'admin')
                <a href="/dev-panel" style="color: #475569; text-decoration: none; padding: 12px 20px; font-size: 15px; border-radius: 6px; font-weight: 500; border: 1px solid #cbd5e1; transition: background-color 0.2s;">
                    Developer Workspace
                </a>
            @endif
        </div>

    </div>
</x-layout>
