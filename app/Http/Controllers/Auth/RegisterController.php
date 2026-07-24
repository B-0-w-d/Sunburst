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
    // Xử lý logic đăng ký tài khoản thành viên mới
    public function register(Request $request)
    {
        // Xác thực dữ liệu đầu vào (tên, email, password, mã kích hoạt,...)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|min:8|confirmed',
            'activation_key' => 'required|string',
            'birthday' => 'nullable|date',
            'instrument' => 'nullable',
        ]);

        // Trả về lỗi 422 nếu dữ liệu không hợp lệ
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        // Khai báo mã kích hoạt dự phòng dùng riêng cho việc debug/test nhanh
        $debugKey = 'SUNBURST';

        // Kiểm tra mã kích hoạt trong database nếu không phải là mã debug
        if ($request->activation_key !== $debugKey) {
            $key = ActivationKey::where('key_value', $request->activation_key)
                ->where('starts_at', '<=', now())
                ->where('expires_at', '>=', now())
                ->first();

            // Trả về lỗi 400 nếu mã không tồn tại hoặc đã hết hạn
            if (!$key) {
                return response()->json(['status' => 'error', 'message' => 'Key invalid or expired'], 400);
            }
        }

        // Tạo mới bản ghi thành viên (Member) trong cơ sở dữ liệu
        $member = Member::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => $request->password,
            'birthday'   => $request->birthday,
            'instrument' => $request->instrument,
            'role'       => 'member',
            'status'     => 'active'
        ]);

        // Tự động đăng nhập cho thành viên vừa đăng ký thành công
        Auth::login($member);

        // Trả về thông báo thành công dưới dạng JSON
        return response()->json(['status' => 'success', 'message' => 'Account created successfully!']);
    }
}
