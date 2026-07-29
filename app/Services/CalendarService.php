<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * Lấy danh sách sự kiện dựa trên các bộ lọc (Tháng/Năm hoặc toàn bộ)
     * kết hợp tự động tạo các sự kiện sinh nhật từ danh sách thành viên.
     *
     * @param mixed $user Thông tin người dùng hiện tại
     * @param array $filters Mảng chứa các điều kiện lọc ('all', 'month', 'year')
     * @return Collection Trả về danh sách sự kiện đã được sắp xếp và loại bỏ trùng lặp
     */
    public function getEventsForUser($user, array $filters)
    {
        $isAll = $filters['all'] ?? false;
        $members = Member::all();
        $birthdayEvents = [];
        $currentYear = date('Y');

        // Trường hợp lấy toàn bộ sự kiện từ trước đến nay
        if ($isAll) {
            $events = Calendar::orderBy('start_time', 'asc')->get();

            // Duyệt qua danh sách thành viên để quy đổi ngày sinh thành sự kiện
            foreach ($members as $member) {
                if (!empty($member->birthday)) {
                    try {
                        $birthday = Carbon::parse($member->birthday);
                        $birthdayEvents[] = $this->formatBirthdayEvent($member, $currentYear, $birthday);
                    } catch (\Exception $ex) {
                        // Bỏ qua nếu định dạng ngày sinh của thành viên không hợp lệ
                    }
                }
            }
            $allEvents = $events->concat($birthdayEvents);
        } else {
            // Trường hợp lấy sự kiện theo tháng và năm cụ thể
            $month = intval($filters['month'] ?? date('m'));
            $year = intval($filters['year'] ?? date('Y'));

            $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0)->startOfMonth()->toDateTimeString();
            $endOfMonth = Carbon::create($year, $month, 1, 0, 0, 0)->endOfMonth()->toDateTimeString();

            // Truy vấn sự kiện trong khoảng thời gian của tháng
            $events = Calendar::where('start_time', '>=', $startOfMonth)
                ->where('start_time', '<=', $endOfMonth)
                ->orderBy('start_time', 'asc')
                ->get();

            // Lọc và thêm các sự kiện sinh nhật trùng với tháng đang xét
            foreach ($members as $member) {
                if (!empty($member->birthday)) {
                    try {
                        $birthday = Carbon::parse($member->birthday);
                        if ($birthday->month == $month) {
                            $birthdayEvents[] = $this->formatBirthdayEvent($member, $year, $birthday);
                        }
                    } catch (\Exception $ex) {
                        // Bỏ qua nếu định dạng ngày sinh lỗi
                    }
                }
            }
            $allEvents = $events->concat($birthdayEvents);
        }

        // Lọc bỏ trùng lặp an toàn dựa trên chuỗi băm (MD5) của tiêu đề, thời gian và loại sự kiện
        return $allEvents->unique(function ($item) {
            $title = is_array($item) ? ($item['title'] ?? '') : ($item->title ?? '');
            $startTime = is_array($item) ? ($item['start_time'] ?? '') : ($item->start_time ?? '');
            $type = is_array($item) ? ($item['type'] ?? '') : ($item->type ?? '');

            return md5(trim($title) . '_' . trim($startTime) . '_' . trim($type));
        })->sortBy('start_time')->values();
    }

    /**
     * Định dạng thông tin sinh nhật của thành viên thành cấu trúc dữ liệu sự kiện chuẩn.
     *
     * @param Member $member Thông tin thành viên
     * @param int $year Năm hiển thị sự kiện
     * @param Carbon $birthday Đối tượng ngày sinh
     * @return array Mảng dữ liệu sự kiện sinh nhật
     */
    private function formatBirthdayEvent($member, $year, Carbon $birthday): array
    {
        return [
            '_id' => 'bday_' . ($member->_id ?? uniqid()),
            'title' => '🎂 Sinh nhật: ' . ($member->name ?? 'Thành viên'),
            'description' => 'Chúc mừng sinh nhật ' . ($member->name ?? 'thành viên') . '!',
            'start_time' => Carbon::create($year, $birthday->month, $birthday->day, 0, 0, 0)->toIso8601String(),
            'end_time' => Carbon::create($year, $birthday->month, $birthday->day, 23, 59, 59)->toIso8601String(),
            'type' => 'birthday',
            'reference_id' => $member->_id ?? null,
        ];
    }

    /**
     * Tạo mới một sự kiện vào cơ sở dữ liệu.
     *
     * @param array $validatedData Dữ liệu đã được validate
     * @param mixed $user Người thực hiện hành động tạo
     * @return Calendar Đối tượng sự kiện vừa được tạo
     */
    public function createEvent(array $validatedData, $user)
    {
        return Calendar::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'] ?? '',
            'start_time' => Carbon::parse($validatedData['start_time'])->format('Y-m-d H:i:s'),
            'end_time' => Carbon::parse($validatedData['end_time'])->format('Y-m-d H:i:s'),
            'type' => $validatedData['type'],
            'reference_id' => $validatedData['reference_id'] ?? null,
            'created_by' => $user->_id ?? $user->id,
        ]);
    }

    /**
     * Cập nhật thông tin một sự kiện đã tồn tại.
     *
     * @param Calendar $event Đối tượng sự kiện cần sửa
     * @param array $validatedData Dữ liệu mới đã qua validate
     * @return Calendar Đối tượng sự kiện sau khi cập nhật
     */
    public function updateEvent(Calendar $event, array $validatedData)
    {
        // Chuyển đổi định dạng thời gian nếu có thay đổi
        if (isset($validatedData['start_time'])) {
            $validatedData['start_time'] = Carbon::parse($validatedData['start_time'])->format('Y-m-d H:i:s');
        }
        if (isset($validatedData['end_time'])) {
            $validatedData['end_time'] = Carbon::parse($validatedData['end_time'])->format('Y-m-d H:i:s');
        }

        $event->update($validatedData);
        return $event;
    }
}
