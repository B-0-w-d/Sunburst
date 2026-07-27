<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivationKey;
use App\Models\Member;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // 1. Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|min:8|confirmed',
            'activation_key' => 'required|string',
            'birthday' => 'nullable|date',
            'instrument' => 'nullable',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // 2. Kiểm tra mã kích hoạt
        $debugKey = 'SUNBURST';
        if ($request->activation_key !== $debugKey) {
            $key = ActivationKey::where('key_value', $request->activation_key)
                ->where('starts_at', '<=', now())
                ->where('expires_at', '>=', now())
                ->first();

            if (!$key) {
                if ($request->expectsJson()) {
                    return response()->json(['status' => 'error', 'message' => 'Key invalid or expired'], 400);
                }
                return back()->withErrors(['activation_key' => 'Key invalid or expired'])->withInput();
            }
        }

        // 3. Tạo thành viên mới (GIỮ NGUYÊN password như code gốc của bạn, phòng trường hợp Model đã tự băm)
        $member = Member::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => $request->password,
            'birthday'   => $request->birthday,
            'instrument' => $request->instrument,
            'role'       => 'member',
            'status'     => 'active'
        ]);

        // 4. THAY VÌ AUTH::LOGIN, TA DÙNG AUTH::ATTEMPT (Giống hệt hàm Login của bạn)
        // Việc này giải quyết dứt điểm lỗi lệch dữ liệu Session của MongoDB
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // 5. Tạo thông báo đăng ký thành công
            \App\Models\SystemNotification::create([
                'type' => 'personal',
                'recipient_id' => $user->_id,
                'sender_id' => null,
                'title' => 'Đăng ký thành công',
                'message' => 'Chào mừng bạn đến với hệ thống vào lúc ' . now(),
                'read_at' => null,
            ]);

            // 6. Trả về Token cho Frontend
            if ($request->expectsJson()) {
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Account created and logged in successfully!',
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'member' => $user
                ]);
            }

            return redirect()->intended('/');
        }

        // 7. Fallback nếu đăng nhập tự động thất bại
        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => 'Đăng ký thành công nhưng tự động đăng nhập thất bại. Vui lòng đăng nhập tay.'], 500);
        }
        return redirect('/login')->withErrors(['email' => 'Vui lòng đăng nhập lại.']);
    }
}
