<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Lấy danh sách thành viên với bộ lọc và sắp xếp.
     */
    public function index(Request $request)
    {
        // Thu thập các tham số lọc dữ liệu từ request
        $filters = array_filter([
            'role' => $request->query('role'),
            'status' => $request->query('status'),
            'instrument' => $request->query('instrument'),
        ]);

        // Thiết lập cấu hình sắp xếp (mặc định theo chiều tăng dần asc)
        $sort = [
            'sortBy'    => $request->query('sortBy'),
            'sortOrder' => $request->query('sortOrder', 'asc'),
        ];

        // Truy vấn danh sách thành viên áp dụng bộ lọc và sắp xếp
        $list = Member::withFilters($filters, $sort)->get();

        // Chuẩn hóa danh sách nhạc cụ của từng thành viên theo thứ tự alphabet
        $list->transform(function ($member) {
            if (isset($member->instrument) && is_array($member->instrument)) {
                $instruments = $member->instrument;
                sort($instruments);
                $member->instrument = array_values($instruments);
            }
            return $member;
        });

        // Trả về dữ liệu dạng JSON nếu request yêu cầu, ngược lại trả về view Blade
        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'count' => $list->count(), 'data' => $list], 200);
        }

        return view('members', ['members' => $list]);
    }

    /**
     * Thêm mới thành viên (Dành cho Admin/Management).
     */
    public function store(Request $request)
    {
        // Kiểm tra xem tên và email có được cung cấp đầy đủ hay không
        if (!$request->has(['name', 'email']) || empty($request->name) || empty($request->email)) {
            return response()->json(['status' => 'error', 'message' => 'Name and Email are required.'], 400);
        }

        // Tạo mới bản ghi thành viên từ toàn bộ dữ liệu request
        $member = Member::create($request->all());

        // Phản hồi kết quả thành công hoặc thất bại ghi database
        return $member
            ? response()->json(['status' => 'success', 'message' => 'Added successfully!'], 201)
            : response()->json(['status' => 'error', 'message' => 'Database write failed.'], 500);
    }

    /**
     * Cập nhật thông tin thành viên.
     */
    public function update(Request $request, $id)
    {
        // Tìm thông tin thành viên theo ID, trả về 404 nếu không tìm thấy
        $member = Member::find($id);
        if (!$member) return response()->json(['status' => 'error', 'message' => 'Member not found.'], 404);

        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        // Kiểm tra quyền hạn: chỉ cho phép chính chủ cập nhật profile của mình hoặc tài khoản thuộc cấp quản lý
        if (!$currentUser || ($currentUser->_id !== $member->_id && !$currentUser->isManagementTier())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        // Lọc ra các trường dữ liệu được phép cập nhật
        $data = $request->only(['name', 'email', 'birthday', 'role', 'instrument', 'phone_number', 'background_image']);

        // Nếu không phải cấp quản lý thì loại bỏ trường role ra khỏi dữ liệu cập nhật
        if (!$currentUser->isManagementTier()) unset($data['role']);

        // Thực hiện cập nhật thông tin vào cơ sở dữ liệu
        return $member->update($data)
            ? response()->json(['status' => 'success', 'message' => 'Updated successfully.'])
            : response()->json(['status' => 'error', 'message' => 'Failed to save.'], 500);
    }

    /**
     * Xóa thông tin thành viên.
     */

    public function destroy($id)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        // Kiểm tra quyền quản lý: chỉ cấp quản lý mới có quyền xóa thành viên
        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        // Tìm kiếm và thực hiện xóa thành viên theo ID
        $member = Member::find($id);
        if (!$member || !$member->delete()) {
            return response()->json(['status' => 'error', 'message' => 'Member not found or already deleted.'], 404);
        }

        // Trả về kết quả xóa thành công
        return response()->json(['status' => 'success', 'message' => 'Member deleted successfully.']);
    }
}
