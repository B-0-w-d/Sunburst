<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ManagementGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        // 2. Use the Auth facade and type-hint
        /** @var \App\Models\Member|null $user */
        $user = Auth::user();

        if ($user && $user->isManagementTier()) {
            return $next($request);
        }

        // If they are just a standard member, block
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Insufficient role clearance.'], 403);
        }

        abort(403, 'Only managers, presidents, or administrators can modify this space.');
    }
}
