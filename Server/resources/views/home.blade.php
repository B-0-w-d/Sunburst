{{-- resources/views/home.blade.php --}}
<x-navbar title="Welcome to Sunburst">

    @auth
        {{-- Logged In View --}}
        <div class="welcome-container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh; text-align: center; font-family: 'Inter', sans-serif; padding: 20px; background-color: #fcfcfd;">
            <h1 style="font-size: 2.75rem; color: #0f172a; margin-bottom: 16px; font-weight: 800; letter-spacing: -0.025em;">
                Welcome to Sunburst
            </h1>
            <p style="font-size: 1.15rem; color: #475569; max-width: 560px; line-height: 1.625; margin-bottom: 32px; font-weight: 400;">
                You have successfully cleared console authentication tiers. Select an environment hub below.
            </p>
            <div style="display: flex; gap: 16px; justify-content: center;">
                <a href="/members" class="btn-save" style="background-color: #4f46e5; color: white; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600;">
                    Go to Members Roster Dashboard
                </a>
                @if(strtolower(auth()->user()->role ?? '') === 'admin')
                    <a href="/dev-panel" style="color: #475569; padding: 12px 20px; border-radius: 6px; border: 1px solid #cbd5e1;">
                        Developer Workspace
                    </a>
                @endif
            </div>
        </div>
    @endauth

    @guest
        {{-- Guest Modal / Login View --}}
        <div style="display: flex; align-items: center; justify-content: center; min-height: 80vh; background-color: #f8fafc;">
            <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center;">
                <h2 style="margin-bottom: 20px;">Access Restricted</h2>
                <p style="margin-bottom: 20px; color: #64748b;">Please log in to manage roster channels.</p>

                <form action="/login" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    <button type="submit" style="width: 100%; background: #4f46e5; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer;">
                        Login
                    </button>
                </form>
            </div>
        </div>
    @endguest

</x-navbar>
