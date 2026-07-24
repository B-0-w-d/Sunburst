<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeyController extends Controller
{
    public function generateKey(Request $request)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $newKey = strtoupper(bin2hex(random_bytes(4)));

        $keyModel = \App\Models\ActivationKey::create([
            'key_value'  => $newKey,
            'starts_at'  => now(),
            'expires_at' => now()->addDays(1)
        ]);

        return response()->json([
            'status'     => 'success',
            'key'        => $keyModel->key_value,
            'expires_at' => $keyModel->expires_at
        ]);
    }
}
