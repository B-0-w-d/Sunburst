<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\ActivationKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemberController
{
    /**
     * Lấy danh sách thành viên với bộ lọc và sắp xếp.
     */
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
            return response()->json(['status' => 'success', 'count' => $list->count(), 'data' => $list], 200);
        }

        return view('members', ['members' => $list]);
    }

    /**
     * Đăng ký thành viên mới (Đã sửa để lưu birthday và instrument).
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|min:8|confirmed',
            'activation_key' => 'required|string',
            'birthday' => 'nullable|date',
            'instrument' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $key = ActivationKey::where('key_value', $request->activation_key)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->first();

        if (!$key) {
            return response()->json(['status' => 'error', 'message' => 'Key invalid or expired'], 400);
        }

        // Đảm bảo dữ liệu được truyền từ request vào Model
        $member = Member::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => $request->password,
            'birthday'   => $request->birthday,
            'instrument' => $request->instrument, // Đã thêm
            'role'       => 'member',
            'status'     => 'active'
        ]);

        Auth::login($member);

        return response()->json(['status' => 'success', 'message' => 'Account created successfully!']);
    }

    /**
     * Thêm mới thành viên (Dành cho Admin/Management).
     */
    public function store(Request $request)
    {
        if (!$request->has(['name', 'email']) || empty($request->name) || empty($request->email)) {
            return response()->json(['status' => 'error', 'message' => 'Name and Email are required.'], 400);
        }

        $member = Member::create($request->all());

        return $member
            ? response()->json(['status' => 'success', 'message' => 'Added successfully!'], 201)
            : response()->json(['status' => 'error', 'message' => 'Database write failed.'], 500);
    }

    /**
     * Cập nhật thông tin thành viên.
     */
    public function update(Request $request, $id)
    {
        $member = Member::find($id);
        if (!$member) return response()->json(['status' => 'error', 'message' => 'Member not found.'], 404);

        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser || ($currentUser->_id !== $member->_id && !$currentUser->isManagementTier())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $data = $request->only(['name', 'email', 'birthday', 'role', 'instrument', 'phone_number', 'background_image']);

        if (!$currentUser->isManagementTier()) unset($data['role']);

        return $member->update($data)
            ? response()->json(['status' => 'success', 'message' => 'Updated successfully.'])
            : response()->json(['status' => 'error', 'message' => 'Failed to save.'], 500);
    }

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

    public function destroy($id)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $member = Member::find($id);
        if (!$member || !$member->delete()) {
            return response()->json(['status' => 'error', 'message' => 'Member not found or already deleted.'], 404);
        }

        return response()->json(['status' => 'success', 'message' => 'Member deleted successfully.']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => 'Logged in successfully.', 'member' => Auth::user()]);
            }

            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

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
