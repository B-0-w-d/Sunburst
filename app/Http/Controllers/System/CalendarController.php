<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Calendar\Event;
use App\Models\Calendar\MemberAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $query = Event::query();
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Lọc sự kiện cho member thường
        if ($currentUser->role !== 'admin' && (!method_exists($currentUser, 'isManagementTier') || !$currentUser->isManagementTier())) {
            $userId = (string) $currentUser->_id;
            $query->where('target_member_ids', 'like', "%{$userId}%");
        }

        $events = $query->get();

        // Đính kèm trạng thái xem member này đã điền lịch rảnh cho sự kiện POLL này chưa
        $isManager = $currentUser->role === 'admin' || (method_exists($currentUser, 'isManagementTier') && $currentUser->isManagementTier());

        $events->transform(function ($event) use ($currentUser, $isManager) {
            if ($event->status === 'POLL') {
                $hasSubmitted = MemberAvailability::where('event_id', (string) $event->_id)
                    ->where('member_id', (string) $currentUser->_id)
                    ->exists();
                $event->has_submitted_availability = $hasSubmitted;
            }
            $event->is_manager = $isManager;
            return $event;
        });

        return response()->json([
            'status' => 'success',
            'count' => $events->count(),
            'data' => $events
        ], 200);
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || (method_exists($currentUser, 'isManagementTier') && !$currentUser->isManagementTier() && $currentUser->role !== 'admin')) {
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
            'target_member_ids' => array_values((array) $request->target_member_ids),
        ];

        if ($request->status === 'POLL') {
            $request->validate(['poll_config' => 'required|array']);
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

        if (!empty($request->target_member_ids) && function_exists('send_system_notification')) {
            foreach ($request->target_member_ids as $memberId) {
                send_system_notification([
                    'type' => 'personal',
                    'recipient_id' => $memberId,
                    'sender_id' => (string) $currentUser->_id,
                    'title' => $request->status === 'POLL' ? 'Khảo sát lịch mới: ' . $event->title : 'Lịch mới đã chốt: ' . $event->title,
                    'message' => $request->status === 'POLL' ? 'Vui lòng vào điền lịch rảnh của bạn.' : 'Sự kiện diễn ra vào lúc ' . $event->start_time,
                ]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Event created successfully!', 'data' => $event], 201);
    }

    public function submitAvailability(Request $request, $eventId)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $event = Event::find($eventId);
        if (!$event || $event->status !== 'POLL') {
            return response()->json(['status' => 'error', 'message' => 'Poll event not found or closed.'], 404);
        }

        $request->validate(['available_slots' => 'required|array']);

        $availability = MemberAvailability::updateOrCreate(
            ['event_id' => (string) $eventId, 'member_id' => (string) $currentUser->_id],
            ['available_slots' => $request->available_slots]
        );

        return response()->json(['status' => 'success', 'message' => 'Availability submitted successfully.', 'data' => $availability]);
    }

    public function getPollReport($eventId)
    {
        $currentUser = Auth::user();
        $isManager = $currentUser && ($currentUser->role === 'admin' || (method_exists($currentUser, 'isManagementTier') && $currentUser->isManagementTier()));

        if (!$isManager) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $event = Event::find($eventId);
        if (!$event || $event->status !== 'POLL') {
            return response()->json(['status' => 'error', 'message' => 'Poll not found.'], 404);
        }

        $availabilities = MemberAvailability::where('event_id', $eventId)->get();
        $slotCounts = [];
        foreach ($availabilities as $item) {
            if (!is_array($item->available_slots)) continue;
            foreach ($item->available_slots as $slot) {
                $slotCounts[$slot] = ($slotCounts[$slot] ?? 0) + 1;
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

    public function confirmPoll(Request $request, $eventId)
    {
        $currentUser = Auth::user();
        $isManager = $currentUser && ($currentUser->role === 'admin' || (method_exists($currentUser, 'isManagementTier') && $currentUser->isManagementTier()));

        if (!$isManager) {
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

        return response()->json(['status' => 'success', 'message' => 'Poll confirmed successfully.', 'data' => $event]);
    }

    public function destroy($eventId)
    {
        $currentUser = Auth::user();
        $isManager = $currentUser && ($currentUser->role === 'admin' || (method_exists($currentUser, 'isManagementTier') && $currentUser->isManagementTier()));

        if (!$isManager) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Event not found.'], 404);
        }

        MemberAvailability::where('event_id', (string) $eventId)->delete();
        $event->delete();

        return response()->json(['status' => 'success', 'message' => 'Event deleted successfully.'], 200);
    }

    public function myLatestAvailability(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $latest = MemberAvailability::where('member_id', (string) $currentUser->_id)
            ->latest('updated_at')
            ->first();

        return response()->json([
            'status' => 'success',
            'available_slots' => $latest ? $latest->available_slots : []
        ]);
    }
    /**
     * Tự động quét và gửi thông báo cho các sự kiện đang diễn ra ngay tại thời điểm hiện tại.
     * Có thể gọi hàm này thông qua một API riêng hoặc thông qua Laravel Scheduler (Cron job).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAndNotifyOngoingEvents()
    {
        $currentUser = Auth::user();
        $now = Carbon::now()->toDateTimeString();

        // Lấy tất cả các sự kiện đã chốt (CONFIRMED) và đang trong khoảng thời gian diễn ra
        $ongoingEvents = Event::where('status', 'CONFIRMED')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->get();

        if ($ongoingEvents->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Hiện tại không có sự kiện nào đang diễn ra.'
            ]);
        }

        $notifiedCount = 0;

        foreach ($ongoingEvents as $event) {
            // Kiểm tra xem sự kiện có danh sách thành viên mục tiêu hay không
            if (!empty($event->target_member_ids) && function_exists('send_system_notification')) {
                foreach ($event->target_member_ids as $memberId) {
                    // Gửi thông báo chi tiết sự kiện đang diễn ra đến từng thành viên
                    send_system_notification([
                        'type' => 'personal',
                        'recipient_id' => $memberId,
                        'sender_id' => $currentUser ? (string) $currentUser->_id : 'system',
                        'title' => 'Sự kiện đang diễn ra: ' . $event->title,
                        'message' => 'Sự kiện "' . $event->title . '" đang diễn ra từ ' . $event->start_time . ' đến ' . $event->end_time . '.',
                    ]);
                    $notifiedCount++;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Đã gửi thông báo cho {$ongoingEvents->count()} sự kiện đang diễn ra.",
            'ongoing_events' => $ongoingEvents
        ]);
    }
}
