<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\Member;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * API Lấy toàn bộ sự kiện trên Calendar theo Tháng/Năm
     * GET /api/calendar?month=04&year=2026
     */
    public function index(Request $request)
    {
        try {
            $isAll = $request->boolean('all');
            $members = Member::all();
            $birthdayEvents = [];

            if ($isAll) {
                $events = Calendar::orderBy('start_time', 'asc')->get();
                $currentYear = date('Y');

                foreach ($members as $member) {
                    if (!empty($member->birthday)) {
                        try {
                            $birthday = Carbon::parse($member->birthday);
                            $birthdayEvents[] = [
                                '_id' => 'bday_' . ($member->_id ?? uniqid()),
                                'title' => '🎂 Sinh nhật: ' . ($member->name ?? 'Thành viên'),
                                'description' => 'Chúc mừng sinh nhật ' . ($member->name ?? 'thành viên') . '!',
                                'start_time' => Carbon::create($currentYear, $birthday->month, $birthday->day, 0, 0, 0)->toIso8601String(),
                                'end_time' => Carbon::create($currentYear, $birthday->month, $birthday->day, 23, 59, 59)->toIso8601String(),
                                'type' => 'birthday',
                                'reference_id' => $member->_id ?? null,
                            ];
                        } catch (\Exception $ex) {
                            // Bỏ qua nếu ngày sinh lỗi format
                        }
                    }
                }
                $allEvents = $events->concat($birthdayEvents);
            } else {
                $month = intval($request->input('month', date('m')));
                $year = intval($request->input('year', date('Y')));

                $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0)->startOfMonth();
                $endOfMonth = Carbon::create($year, $month, 1, 0, 0, 0)->endOfMonth();

                // Sử dụng cú pháp truy vấn an toàn khoảng thời gian
                $events = Calendar::whereBetween('start_time', [
                    $startOfMonth->toIso8601String(),
                    $endOfMonth->toIso8601String()
                ])->orderBy('start_time', 'asc')->get();

                foreach ($members as $member) {
                    if (!empty($member->birthday)) {
                        try {
                            $birthday = Carbon::parse($member->birthday);
                            if ($birthday->month == $month) {
                                $birthdayEvents[] = [
                                    '_id' => 'bday_' . ($member->_id ?? uniqid()),
                                    'title' => '🎂 Sinh nhật: ' . ($member->name ?? 'Thành viên'),
                                    'description' => 'Chúc mừng sinh nhật ' . ($member->name ?? 'thành viên') . '!',
                                    'start_time' => Carbon::create($year, $month, $birthday->day, 0, 0, 0)->toIso8601String(),
                                    'end_time' => Carbon::create($year, $month, $birthday->day, 23, 59, 59)->toIso8601String(),
                                    'type' => 'birthday',
                                    'reference_id' => $member->_id ?? null,
                                ];
                            }
                        } catch (\Exception $ex) {
                            // Bỏ qua nếu ngày sinh lỗi format
                        }
                    }
                }
                $allEvents = $events->concat($birthdayEvents);
            }

            // Lọc bỏ trùng lặp an toàn
            $sortedEvents = $allEvents->unique(function ($item) {
                $title = is_array($item) ? ($item['title'] ?? '') : ($item->title ?? '');
                $startTime = is_array($item) ? ($item['start_time'] ?? '') : ($item->start_time ?? '');
                $type = is_array($item) ? ($item['type'] ?? '') : ($item->type ?? '');

                return md5(trim($title) . '_' . trim($startTime) . '_' . trim($type));
            })->sortBy('start_time')->values();

            return response()->json([
                'success' => true,
                'data' => $sortedEvents
            ]);
        } catch (\Exception $e) {
            // Trả về lỗi chi tiết giúp dễ debug nếu còn vấn đề khác
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * API BQL thêm một sự kiện tùy chỉnh lên Calendar chung
     * POST /api/calendar
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Kiểm tra quyền BQL
        if (!$user || !method_exists($user, 'isManagementTier') || !$user->isManagementTier()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thêm sự kiện lên lịch chung.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'type' => 'required|string|in:show,rehearsal,event,other',
            'reference_id' => 'nullable|string',
        ]);

        $event = Calendar::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'start_time' => Carbon::parse($validated['start_time'])->toIso8601String(),
            'end_time' => Carbon::parse($validated['end_time'])->toIso8601String(),
            'type' => $validated['type'],
            'reference_id' => $validated['reference_id'] ?? null,
            'created_by' => $user->_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm sự kiện vào lịch thành công!',
            'data' => $event
        ], 201);
    }
    /**
     * API BQL chỉnh sửa sự kiện trên Calendar
     * PUT /api/calendar/{id}
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Kiểm tra quyền BQL
        if (!$user || !method_exists($user, 'isManagementTier') || !$user->isManagementTier()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa sự kiện này.'
            ], 403);
        }

        $event = Calendar::find($id);
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sự kiện trên lịch.'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after_or_equal:start_time',
            'type' => 'sometimes|required|string|in:show,rehearsal,event,other',
            'reference_id' => 'nullable|string',
        ]);

        // Cập nhật dữ liệu nếu có truyền lên
        if (isset($validated['start_time'])) {
            $validated['start_time'] = Carbon::parse($validated['start_time'])->toIso8601String();
        }
        if (isset($validated['end_time'])) {
            $validated['end_time'] = Carbon::parse($validated['end_time'])->toIso8601String();
        }

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sự kiện thành công!',
            'data' => $event
        ]);
    }

    /**
     * API BQL xóa sự kiện trên Calendar
     * DELETE /api/calendar/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Kiểm tra quyền BQL
        if (!$user || !method_exists($user, 'isManagementTier') || !$user->isManagementTier()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa sự kiện này.'
            ], 403);
        }

        $event = Calendar::find($id);
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sự kiện trên lịch.'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa sự kiện thành công!'
        ]);
    }
}
