// =========================================================================
// API SERVICE: GIAO TIẾP BACKEND CHO MODULE LỊCH TRÌNH
// =========================================================================

import { parseLocalDateTime, headers } from './utils.js';

/**
 * Tải toàn bộ danh sách lịch (cả Confirmed và Poll) từ server
 */
export async function fetchAllEventsApi() {
    const [resConfirmed, resPoll] = await Promise.all([
        fetch('/api/calendar?status=CONFIRMED', { headers: headers }),
        fetch('/api/calendar?status=POLL', { headers: headers })
    ]);

    const resultConfirmed = await resConfirmed.json();
    const resultPoll = await resPoll.json();

    return {
        confirmedEvents: resultConfirmed.data || [],
        pollEvents: resultPoll.data || []
    };
}

/**
 * Xóa một sự kiện theo ID
 */
export async function deleteEventApi(eventId) {
    const res = await fetch(`/api/calendar/${eventId}`, {
        method: 'DELETE',
        headers: headers
    });
    const result = await res.json();
    if (!res.ok) {
        throw new Error(result.message || 'Không thể xóa sự kiện.');
    }
    return result;
}

/**
 * Lưu lịch rảnh cá nhân cho sự kiện khảo sát
 */
export async function saveAvailabilityApi(activeEventId, availableSlots) {
    const res = await fetch(`/api/calendar/${activeEventId}/availability`, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ available_slots: availableSlots })
    });
    const result = await res.json();
    if (!res.ok) {
        throw new Error(result.message || 'Lỗi gửi lịch rảnh.');
    }
    return result;
}

/**
 * Lấy lịch rảnh mới nhất của cá nhân
 */
export async function fetchMyLatestAvailabilityApi() {
    const res = await fetch(`/api/calendar/my-latest-availability`, { headers: headers });
    if (!res.ok) return { available_slots: [] };
    return await res.json();
}

/**
 * Lấy báo cáo tổng hợp khảo sát (Poll report)
 */
export async function fetchPollReportApi(activeEventId) {
    const res = await fetch(`/api/calendar/${activeEventId}/poll-report`, { headers: headers });
    if (!res.ok) {
        throw new Error('Không thể tải báo cáo khảo sát.');
    }
    return await res.json();
}

/**
 * Chốt lịch chính thức từ khảo sát
 */
export async function confirmPollApi(activeEventId, startTimeFormatted, endTimeFormatted) {
    const res = await fetch(`/api/calendar/${activeEventId}/confirm-poll`, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ start_time: startTimeFormatted, end_time: endTimeFormatted })
    });
    const result = await res.json();
    if (!res.ok) {
        throw new Error(result.message || 'Lỗi chốt lịch.');
    }
    return result;
}

/**
 * Tạo mới sự kiện
 */
export async function createEventApi(payload) {
    const res = await fetch('/api/calendar', {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!res.ok) {
        throw new Error(result.message || 'Lỗi tạo sự kiện.');
    }
    return result;
}

/**
 * Cập nhật sự kiện
 */
export async function updateEventApi(eventId, payload) {
    const res = await fetch(`/api/calendar/${eventId}`, {
        method: 'PUT',
        headers: headers,
        body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (!res.ok) {
        throw new Error(result.message || 'Lỗi cập nhật sự kiện.');
    }
    return result;
}
