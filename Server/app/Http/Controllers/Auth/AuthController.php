<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            /** @var \App\Models\Member $user */
            $user = Auth::user();

            if ($request->expectsJson()) {
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Logged in successfully.',
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'member' => $user
                ]);
            }

            return redirect()->intended('/');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        // 1. Lấy chuỗi PlainText Token từ Header (Ví dụ: Bearer 1|abcxyz...)
        $tokenString = $request->bearerToken();

        if ($tokenString) {
            // Tách ID và chuỗi hash nếu token có định dạng "id|token" (Đặc trưng của Sanctum)
            if (strpos($tokenString, '|') !== false) {
                [$id, $tokenString] = explode('|', $tokenString, 2);
            }

            // Tìm token bằng chuỗi SHA256 trong MongoDB và xóa nó đi
            $hashedToken = hash('sha256', $tokenString);
            \App\Models\PersonalAccessToken::where('token', $hashedToken)->delete();
        }

        // 2. Xử lý xóa Session (Nếu ứng dụng có chạy song song cả Web Session)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Phản hồi kết quả cho phía Client
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully.'
            ]);
        }

        return redirect('/');
    }


    /**
     * THÊM HÀM NÀY VÀO ĐÂY ĐỂ XEM DANH SÁCH AI ĐANG CÓ TOKEN ĐĂNG NHẬP
     */
    public function getActiveSessions(Request $request)
    {
        $tokens = PersonalAccessToken::with('tokenable')->get();

        $activeUsers = [];

        foreach ($tokens as $token) {
            $member = $token->tokenable; // Đối tượng Member sở hữu token

            if ($member) {
                $activeUsers[] = [
                    'id' => $member->_id,
                    'member_name' => $member->name,
                    'member_email' => $member->email,
                    'role' => $member->role,
                    'token_name' => $token->name,
                    'last_used_at' => $token->last_used_at,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $activeUsers
        ]);
    }
}
