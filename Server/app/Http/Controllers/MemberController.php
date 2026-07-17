<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class MemberController
{
    // Query Operation
    public function index(Request $request)
    {
        $filters = array_filter([
            'role' => $request->query('role'),
            'status' => $request->query('status'),
            'instrument' => $request->query('instrument'),
        ]);

        $sort = [
            'sortBy'    => $request->query('sortBy'),
            'sortOrder' => $request->query('sortOrder', 'asc'),
        ];

        $list = Member::withFilters($filters, $sort)->get();

        $list->transform(function ($member) {
            if (isset($member->instrument) && is_array($member->instrument)) {
                $instruments = $member->instrument;
                sort($instruments);
                $member->instrument = array_values($instruments);
            }
            return $member;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'count'  => $list->count(),
                'data'   => $list
            ], 200);
        }

        return view('members', ['members' => $list]);
    }

    // Create Operation
    public function store(Request $request)
    {
        if (!$request->has(['name', 'email']) || empty($request->name) || empty($request->email)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Name and Email are strictly required.'
            ], 400);
        }

        $member = Member::create($request->all());

        if ($member) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Added successfully!'
            ], 201);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Database write failed.'
        ], 500);
    }

    // Update Operation
    public function update(Request $request, $id)

    {
        \Log::info("Is User Logged In?: " . \Auth::check());
        \Log::info("User ID: " . \Auth::id());

        if (!\Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'User is not logged in'], 401);
        }
        // 1. Attempt to find the member
        $member = Member::find($id);

        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Member not found.'], 404);
        }

        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        if ($currentUser->_id !== $member->_id && !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        // ... proceed with update
        $data = $request->only(['name', 'email', 'birthday', 'role', 'instrument']);

        if (!$currentUser->isManagementTier()) {
            unset($data['role']);
        }

        if ($member->update($data)) {
            return response()->json(['status' => 'success', 'message' => 'Updated successfully.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to save changes.'], 500);
    }

    // Delete Operation
    public function destroy($id)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $member = Member::find($id);
        if (!$member || !$member->delete()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found or already deleted.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Member deleted successfully.'
        ]);
    }

    // Login Operation
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logged in successfully.',
                    'member' => Auth::user()
                ]);
            }

            return redirect()->intended('/');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // Logout Operation
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully.'
            ]);
        }

        return redirect('/');
    }
}
