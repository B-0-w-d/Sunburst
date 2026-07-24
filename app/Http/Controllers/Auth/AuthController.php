<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PersonalAccessToken;
use App\Models\Member;

class AuthController extends Controller
{
    // Xử lý logic đăng nhập người dùng (hỗ trợ cả Web Session và API Token Sanctum)
    public function login(Request $request)
    {
        // Xác thực dữ liệu đầu vào (email và password)
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Kiểm tra thông tin đăng nhập với cơ sở dữ liệu
        if (Auth::attempt($credentials)) {
            // Tái tạo session để bảo mật
            $request->session()->regenerate();

            /** @var \App\Models\Member $user */
            $user = Auth::user();

            // Tạo bản ghi thông báo cá nhân khi đăng nhập thành công
            \App\Models\SystemNotification::create([
                'type' => 'personal',
                'recipient_id' => $user->_id,
                'sender_id' => null,
                'title' => 'Đăng nhập thành công',
                'message' => 'Bạn vừa đăng nhập vào hệ thống vào lúc ' . now(),
                'read_at' => null,
            ]);

            // Nếu request là API (expectsJson), tạo token Sanctum và trả về JSON
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

            // Nếu là web thông thường, chuyển hướng về trang chủ
            return redirect()->intended('/');
        }

        // Trả về lỗi nếu đăng nhập thất bại (phân biệt giữa API và Web)
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    // Xử lý đăng xuất (xóa Token API và hủy Session)
    public function logout(Request $request)
    {
        // Lấy token dạng Bearer từ Header của request
        $tokenString = $request->bearerToken();

        if ($tokenString) {
            // Tách ID và chuỗi token nếu đúng định dạng Sanctum (id|token)
            if (strpos($tokenString, '|') !== false) {
                [$id, $tokenString] = explode('|', $tokenString, 2);
            }

            // Hash token bằng SHA256 và xóa khỏi cơ sở dữ liệu MongoDB
            $hashedToken = hash('sha256', $tokenString);
            \App\Models\PersonalAccessToken::where('token', $hashedToken)->delete();
        }

        // Hủy bỏ Web Session hiện tại
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Phản hồi kết quả đăng xuất (JSON hoặc chuyển hướng web)
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully.'
            ]);
        }

        return redirect('/');
    }

    // Lấy danh sách toàn bộ các phiên đăng nhập đang hoạt động (active sessions) qua token
    public function getActiveSessions(Request $request)
    {
        // Lấy danh sách token kèm theo thông tin quan hệ tokenable (thành viên sở hữu)
        $tokens = PersonalAccessToken::with('tokenable')->get();

        $activeUsers = [];

        foreach ($tokens as $token) {
            $member = $token->tokenable;

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

        // Trả về danh sách phiên đăng nhập dưới dạng JSON
        return response()->json([
            'status' => 'success',
            'data' => $activeUsers
        ]);
    }
}
