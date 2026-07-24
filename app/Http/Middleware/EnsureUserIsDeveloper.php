<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsDeveloper
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if the environment bypass is enabled
        if (app()->environment('local') && env('ALLOW_DEV_BYPASS') === true) {
            return $next($request);
        }

        // 2. Use the Auth facade to clear the Intelephense warning
        if (!Auth::check() || strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Unauthorized access to Dev System Controls.');
        }

        return $next($request);
    }
}
