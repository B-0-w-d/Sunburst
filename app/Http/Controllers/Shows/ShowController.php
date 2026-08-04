<?php

namespace App\Http\Controllers\Shows;

use App\Http\Controllers\Controller;
use App\Models\Show;
use App\Models\Setlist;
use App\Models\Calendar\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ShowController extends Controller
{
    // 1. Lấy danh sách Show
        public function index()
        {
            return response()->json(['status' => 'success', 'data' => Show::with('setlist.song')->get()]);
        }

        // 2. Tạo Show mới và tự động đồng bộ sang Lịch (Event)
        public function store(Request $request)
        {
            $request->validate([
                'title' => 'required|string',
                'date' => 'required|date',
                'location' => 'required|string',
            ]);

            // 1. Tạo Show
            $show = Show::create([
                'title' => $request->input('title'),
                'date' => $request->input('date'),
                'location' => $request->input('location'),
                'organizer_id' => (string) Auth::id(),
            ]);

            // 2. Tự động tạo một Event loại SHOW tương ứng trên lịch calendar
            Event::create([
                'organizer_id' => (string) Auth::id(),
                'title' => $show->title,
                'type' => 'SHOW',        // Mặc định type là SHOW
                'status' => 'CONFIRMED',
                'start_time' => $show->date,
                'end_time' => Carbon::parse($show->date)->addHours(3),
                'target_member_ids' => [],
            ]);

            return response()->json(['status' => 'success', 'message' => 'Tạo show thành công và đã đồng bộ lên lịch!', 'data' => $show], 201);
        }

        // 3. Xem chi tiết Show (kèm các bài hát trong setlist và thông tin lịch tập)
        public function show($showId)
        {
            $show = Show::with(['setlist.song', 'setlist.event'])->find($showId);
            if (!$show) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy show.'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $show]);
        }

        // 4. Thêm bài hát từ kho vào Setlist của Show (Hỗ trợ chọn nhiều bài)
        public function addSongsToSetlist(Request $request, $showId)
        {
            $songIds = $request->input('song_ids', []);
            if (empty($songIds)) {
                return response()->json(['status' => 'error', 'message' => 'Vui lòng chọn ít nhất một bài hát.'], 400);
            }

            foreach ($songIds as $songId) {
                Setlist::firstOrCreate([
                    'show_id' => $showId,
                    'song_id' => $songId,
                ], [
                    'description' => null,
                    'target_member_ids' => [],
                ]);
            }

            return response()->json(['status' => 'success', 'message' => 'Đã thêm bài hát vào setlist thành công!'], 201);
        }

        // 5. Cập nhật thành viên hoặc ghi chú riêng cho bài hát trong Setlist
        public function updateSetlistSongItem(Request $request, $setlistId)
        {
            $setlistEntry = Setlist::find($setlistId);
            if (!$setlistEntry) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bản ghi setlist.'], 404);
            }

            $setlistEntry->update([
                'description' => $request->input('description', $setlistEntry->description),
                'target_member_ids' => $request->input('target_member_ids', $setlistEntry->target_member_ids),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cập nhật thành công!', 'data' => $setlistEntry]);
        }

        // 6. Xóa bài hát khỏi Setlist
        public function removeSongFromSetlist($setlistId)
        {
            $setlistEntry = Setlist::find($setlistId);
            if (!$setlistEntry) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bản ghi setlist.'], 404);
            }

            $setlistEntry->delete();
            return response()->json(['status' => 'success', 'message' => 'Đã xóa bài hát khỏi setlist.']);
        }
    /**
     * Tạo lịch tập (hoặc lịch diễn) cho một bài hát trong Setlist của Show,
     * kèm theo logic kiểm tra ràng buộc thời gian không được vượt quá ngày diễn show.
     */
    public function storeRehearsalEvent(Request $request, $setlistId)
    {
        // 1. Tìm bản ghi trong Setlist và lấy thông tin Show liên quan
        $setlistEntry = Setlist::with('show')->find($setlistId);
        if (!$setlistEntry) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bài hát trong setlist.'], 404);
        }

        $showDate = Carbon::parse($setlistEntry->show->date);
        $eventStartTime = Carbon::parse($request->input('start_time'));

        // 2. LOGIC RÀNG BUỘC THỜI GIAN: Lịch tập không được diễn ra sau hoặc trùng ngày show
        if ($eventStartTime->greaterThanOrEqualTo($showDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi ràng buộc: Không thể tạo lịch tập diễn ra sau hoặc trùng với thời gian diễn ra Show (' . $showDate->format('d/m/Y H:i') . ').'
            ], 422);
        }

        // 3. Tiến hành tạo bản ghi Event mới (tương thích chuẩn schema Event hiện tại của bạn)
        $event = Event::create([
            'organizer_id'      => Auth::id() ? (string) Auth::id() : $setlistEntry->show->organizer_id,
            'title'             => $request->input('title', '[Tập luyện] Bài hát - ' . $setlistEntry->show->title),
            'type'              => $request->input('type', 'PRACTICE'), // Có thể là PRACTICE hoặc SHOW
            'status'            => 'CONFIRMED',
            'target_member_ids' => $setlistEntry->target_member_ids ?? [], // Lấy danh sách thành viên được phân công riêng cho bài này
            'poll_config'       => $request->input('poll_config'),
            'start_time'        => $request->input('start_time'),
            'end_time'          => $request->input('end_time'),
        ]);

        // 4. Cập nhật ngược event_id vào bảng Setlist để liên kết chặt chẽ
        $setlistEntry->update([
            'event_id' => $event->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo lịch tập thành công và đã liên kết với bài hát!',
            'data' => $event
        ], 201);
    }
}
