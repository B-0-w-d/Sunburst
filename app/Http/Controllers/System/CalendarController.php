<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Calendar\Event;
use App\Models\Calendar\MemberAvailability;
use App\Models\Calendar\Invitation;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * 1. Lấy danh sách lịch hoặc khảo sát
     */
    public function index(Request $request)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $status = $request->query('status');
        $query = Event::query();

        if ($status) {
            $query->where('status', $status);
        }

        // Nếu không phải management, chỉ lấy các sự kiện mà thành viên nằm trong target_member_ids
        if (!$currentUser->isManagementTier()) {
            $query->where('target_member_ids', 'contains', (string) $currentUser->_id);
        }

        $events = $query->get();

        return response()->json([
            'status' => 'success',
            'count' => $events->count(),
            'data' => $events
        ], 200);
    }

    /**
     * 2. Admin tạo phiên khảo sát (POLL) hoặc tạo lịch cố định (CONFIRMED)
     */
    public function store(Request $request)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:PRACTICE,MEETING,SHOW,EVENT',
            'status' => 'required|in:POLL,CONFIRMED',
            'target_member_ids' => 'required|array',
        ]);

        $eventData = [
            'organizer_id' => (string) $currentUser->_id,
            'title' => $request->title,
            'type' => $request->type,
            'status' => $request->status,
            'target_member_ids' => $request->target_member_ids,
        ];

        if ($request->status === 'POLL') {
            $request->validate([
                'poll_config' => 'required|array',
            ]);
            $eventData['poll_config'] = $request->poll_config;
        } else {
            $request->validate([
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
            ]);
            $eventData['start_time'] = $request->start_time;
            $eventData['end_time'] = $request->end_time;
        }

        $event = Event::create($eventData);

        // Gửi thông báo cho các thành viên được chỉ định
        foreach ($request->target_member_ids as $memberId) {
            SystemNotification::create([
                'type' => 'personal',
                'recipient_id' => $memberId,
                'sender_id' => (string) $currentUser->_id,
                'title' => $request->status === 'POLL' ? 'Khảo sát lịch mới: ' . $event->title : 'Lịch mới đã chốt: ' . $event->title,
                'message' => $request->status === 'POLL' ? 'Vui lòng vào điền lịch rảnh của bạn.' : 'Sự kiện diễn ra vào lúc ' . $event->start_time,
                'read_at' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Event created successfully!',
            'data' => $event
        ], 201);
    }

    /**
     * 3. Thành viên gửi khoảng thời gian rảnh của mình cho phiên khảo sát
     */
    public function submitAvailability(Request $request, $eventId)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $event = Event::find($eventId);
        if (!$event || $event->status !== 'POLL') {
            return response()->json(['status' => 'error', 'message' => 'Poll event not found or closed.'], 404);
        }

        if (!in_array((string) $currentUser->_id, $event->target_member_ids ?? [])) {
            return response()->json(['status' => 'error', 'message' => 'You are not targeted for this poll.'], 403);
        }

        $request->validate([
            'available_slots' => 'required|array',
        ]);

        $availability = MemberAvailability::updateOrCreate(
            [
                'event_id' => (string) $eventId,
                'member_id' => (string) $currentUser->_id,
            ],
            [
                'available_slots' => $request->available_slots,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Availability submitted successfully.',
            'data' => $availability
        ]);
    }

    /**
     * 4. Admin xem báo cáo tổng hợp thời gian rảnh (Overlap / Heatmap)
     */
    public function getPollReport($eventId)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $event = Event::find($eventId);
        if (!$event || $event->status !== 'POLL') {
            return response()->json(['status' => 'error', 'message' => 'Poll not found.'], 404);
        }

        $availabilities = MemberAvailability::where('event_id', $eventId)->get();

        $slotCounts = [];
        foreach ($availabilities as $item) {
            foreach ($item->available_slots as $slot) {
                if (!isset($slotCounts[$slot])) {
                    $slotCounts[$slot] = 0;
                }
                $slotCounts[$slot]++;
            }
        }

        arsort($slotCounts);

        return response()->json([
            'status' => 'success',
            'target_count' => count($event->target_member_ids ?? []),
            'submitted_count' => $availabilities->count(),
            'slot_statistics' => $slotCounts,
            'raw_availabilities' => $availabilities
        ]);
    }

    /**
     * 5. Admin chốt lịch từ kết quả khảo sát (Chuyển POLL -> CONFIRMED)
     */
    public function confirmPoll(Request $request, $eventId)
    {
        /** @var \App\Models\Member $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->isManagementTier()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $event = Event::find($eventId);
        if (!$event || $event->status !== 'POLL') {
            return response()->json(['status' => 'error', 'message' => 'Poll not found or already confirmed.'], 404);
        }

        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $event->update([
            'status' => 'CONFIRMED',
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // Gửi thông báo lịch đã chính thức được chốt tới các thành viên
        if (!empty($event->target_member_ids)) {
            foreach ($event->target_member_ids as $memberId) {
                SystemNotification::create([
                    'type' => 'personal',
                    'recipient_id' => $memberId,
                    'sender_id' => (string) $currentUser->_id,
                    'title' => 'Lịch đã được chốt: ' . $event->title,
                    'message' => 'Sự kiện đã được chốt lịch vào lúc ' . $request->start_time,
                    'read_at' => null,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Poll confirmed and schedule finalized successfully.',
            'data' => $event
        ]);
    }
}
