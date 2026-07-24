<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang chỉnh sửa thông tin cá nhân của thành viên đang đăng nhập.
     */
    public function editProfile()
    {
        // Trả về view profile kèm theo thông tin user hiện tại
        return view('components.profile', ['member' => Auth::user()]);
    }

    // Xử lý logic cập nhật thông tin cá nhân
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Member $member */
        $member = Auth::user();

        // Xác thực dữ liệu đầu vào, bỏ qua kiểm tra unique email cho chính user hiện tại qua ID MongoDB (_id)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->_id . ',_id',
            'birthday' => 'nullable|date',
            'instrument' => 'nullable',
            'role' => 'nullable|string',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Cập nhật các trường thông tin cơ bản
        $member->name = $request->name;
        $member->email = $request->email;
        $member->birthday = $request->birthday;
        $member->instrument = $request->instrument;

        // Chỉ cho phép cập nhật vai trò (role) nếu user thuộc cấp quản lý
        if ($member->isManagementTier()) {
            $member->role = $request->role;
        }

        // Cập nhật mật khẩu mới nếu người dùng có điền vào form
        if ($request->filled('password')) {
            $member->password = $request->password;
        }

        // Lưu thay đổi vào cơ sở dữ liệu và chuyển hướng lại trang trước kèm thông báo thành công
        $member->save();
        return back()->with('success', 'Profile updated successfully.');
    }
}
