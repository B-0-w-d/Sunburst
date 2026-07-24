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

    /**
     * Xóa thông tin thành viên.
     */

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
}
