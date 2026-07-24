<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeyController extends Controller
{
    // Xử lý tạo mã kích hoạt mới (yêu cầu quyền quản lý - management tier)
    public function generateKey(Request $request)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        // Kiểm tra xem người dùng đã đăng nhập và có thuộc cấp quản lý hay không, nếu không trả về lỗi 403
        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Tạo một mã ngẫu nhiên dạng chuỗi hex (4 byte) và chuyển thành chữ in hoa
        $newKey = strtoupper(bin2hex(random_bytes(4)));

        // Lưu mã kích hoạt mới vào database với thời hạn hiệu lực là 1 ngày kể từ thời điểm tạo
        $keyModel = \App\Models\ActivationKey::create([
            'key_value'  => $newKey,
            'starts_at'  => now(),
            'expires_at' => now()->addDays(1)
        ]);

        // Trả về kết quả JSON thành công kèm theo giá trị mã và thời gian hết hạn
        return response()->json([
            'status'     => 'success',
            'key'        => $keyModel->key_value,
            'expires_at' => $keyModel->expires_at
        ]);
    }
}
