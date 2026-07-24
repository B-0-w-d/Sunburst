<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Cập nhật thông tin cá nhân thành viên.
     */
    public function editProfile()
    {
        return view('components.profile', ['member' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Member $member */
        $member = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->_id . ',_id',
            'birthday' => 'nullable|date',
            'instrument' => 'nullable',
            'role' => 'nullable|string',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $member->name = $request->name;
        $member->email = $request->email;
        $member->birthday = $request->birthday;
        $member->instrument = $request->instrument;

        if ($member->isManagementTier()) {
            $member->role = $request->role;
        }

        if ($request->filled('password')) {
            $member->password = $request->password;
        }

        $member->save();
        return back()->with('success', 'Profile updated successfully.');
    }
}
