// =========================================================================
// FILE CHÍNH: ĐIỀU PHỐI SỰ KIỆN & QUẢN LÝ GIAO DIỆN LỊCH
// =========================================================================

import { buildPollMatrix, buildHeatmapMatrix } from './poll.js';
import { renderMiniCalendar, initMiniCalendarNav, currentMiniMonthDate } from './miniCalendar.js';
import { renderWeeklyCalendar, initWeeklyCalendarNav, currentWeekStartDate } from './weeklyCalendar.js';
import { renderEventList, renderUpcomingEvents } from './eventRenderer.js';
import { initCreateEventModalLogic, actionBindWeeklyEventClickForCreate } from './createEventModal.js';
import './editEventModal.js'; // Module xử lý modal chỉnh sửa

import {
    fetchAllEventsApi,
    saveAvailabilityApi,
    fetchMyLatestAvailabilityApi,
    fetchPollReportApi,
    confirmPollApi
} from './eventApi.js';

document.addEventListener('DOMContentLoaded', function () {
    const confirmedContainer = document.getElementById('confirmedEventList');
    const pollContainer = document.getElementById('pollEventList');

    let activeEventId = null;
    let cachedConfirmedEvents = [];

    // Gán hàm global để các module con gọi lại khi cần reload dữ liệu
    window.loadAllEvents = loadAllEvents;

    // Khởi chạy hệ thống
    loadAllEvents();
    initCreateEventModalLogic();

    // Khởi tạo điều hướng lịch
    initMiniCalendarNav(() => renderMiniCalendar(cachedConfirmedEvents, handleMiniDateClick));
    initWeeklyCalendarNav(() => renderWeeklyCalendar(cachedConfirmedEvents, handleWeeklyEventClick));

    if (typeof initInstrumentFilterOptions === 'function') {
        initInstrumentFilterOptions('filterInstrumentSelector');
    }

    async function loadAllEvents() {
        try {
            const { confirmedEvents, pollEvents } = await fetchAllEventsApi();

            if (confirmedContainer) renderEventList(confirmedContainer, confirmedEvents, 'confirmed');
            if (pollContainer) renderEventList(pollContainer, pollEvents, 'poll');

            cachedConfirmedEvents = confirmedEvents;
            renderUpcomingEvents(cachedConfirmedEvents, loadAllEvents);
            renderWeeklyCalendar(cachedConfirmedEvents, handleWeeklyEventClick);
            renderMiniCalendar(cachedConfirmedEvents, handleMiniDateClick);

            // ---> CẬP NHẬT TRỰC TIẾP Ô SỐ 8 TẠI ĐÂY <---
            renderNearestEventWidget(cachedConfirmedEvents);

            bindPollActionListeners();
        } catch (err) {
            console.error('Lỗi tải danh sách lịch:', err);
        }
    }

    // Hàm xử lý hiển thị sự kiện gần nhất / đang diễn ra cho Ô số 8
    function renderNearestEventWidget(events) {
        const widgetContent = document.getElementById('widgetEventContent');
        const statusBadge = document.getElementById('widgetStatusBadge');
        if (!widgetContent) return;

        if (!events || events.length === 0) {
            if (statusBadge) statusBadge.style.display = 'none';
            widgetContent.innerHTML = `<p style="font-size: 13px; color: #94a3b8; font-style: italic; margin: 0; text-align: center;">Không có sự kiện sắp tới.</p>`;
            return;
        }

        const now = new Date();

        // Sắp xếp các sự kiện theo thời gian bắt đầu
        const sortedEvents = [...events].sort((a, b) => new Date(a.start_time || a.start) - new Date(b.start_time || b.start));

        // Tìm sự kiện đang diễn ra hoặc sắp tới
        let targetEvent = null;
        let isOngoing = false;

        for (let ev of sortedEvents) {
            const start = new Date(ev.start_time || ev.start);
            const end = new Date(ev.end_time || ev.end);

            if (now >= start && now <= end) {
                targetEvent = ev;
                isOngoing = true;
                break;
            } else if (start > now) {
                targetEvent = ev;
                isOngoing = false;
                break;
            }
        }

        // Nếu tất cả sự kiện đã diễn ra trong quá khứ, lấy sự kiện cuối cùng
        if (!targetEvent && sortedEvents.length > 0) {
            targetEvent = sortedEvents[sortedEvents.length - 1];
        }

        if (!targetEvent) {
            if (statusBadge) statusBadge.style.display = 'none';
            widgetContent.innerHTML = `<p style="font-size: 13px; color: #94a3b8; font-style: italic; margin: 0; text-align: center;">Không có sự kiện sắp tới.</p>`;
            return;
        }

        // Cấu hình hiển thị Badge trạng thái ở góc trên bên phải
        if (statusBadge) {
            statusBadge.style.display = 'inline-flex';
            statusBadge.style.alignItems = 'center';
            statusBadge.style.gap = '4px';
            statusBadge.style.padding = '4px 10px';
            statusBadge.style.borderRadius = '20px';
            statusBadge.style.fontSize = '11px';
            statusBadge.style.fontWeight = '600';

            if (isOngoing) {
                statusBadge.style.backgroundColor = '#fef2f2';
                statusBadge.style.color = '#dc2626';
                statusBadge.style.border = '1px solid #fecaca';
                statusBadge.innerHTML = '🔥 Đang diễn ra';
            } else {
                statusBadge.style.backgroundColor = '#f0fdf4';
                statusBadge.style.color = '#16a34a';
                statusBadge.style.border = '1px solid #bbf7d0';
                statusBadge.innerHTML = '⏰ Sắp diễn ra';
            }
        }

        const d = new Date(targetEvent.start_time || targetEvent.start);
        const formattedDate = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} - ${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
        const dotColor = '#6366f1';

        widgetContent.innerHTML = `
            <div style="display: flex; flex-direction: column; justify-content: center; gap: 10px; width: 100%; height: 100%;">
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <span style="background-color: ${dotColor}; flex-shrink: 0; width: 10px; height: 10px; border-radius: 50%; margin-top: 6px; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);"></span>
                    <div style="display: flex; flex-direction: column; overflow: hidden; gap: 2px; width: 100%;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 15px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${targetEvent.title || targetEvent.name || 'Sự kiện không tên'}">${targetEvent.title || targetEvent.name || 'Sự kiện không tên'}</span>
                            ${targetEvent.type ? `<span style="font-size: 10px; font-weight: 600; padding: 2px 6px; background: #f1f5f9; color: #475569; border-radius: 4px; flex-shrink: 0;">${targetEvent.type}</span>` : ''}
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="font-size: 14px;">📅</span>
                    <span style="font-size: 13px; font-weight: 600; color: #475569;">${formattedDate}</span>
                </div>
            </div>
        `;
    }

    function handleMiniDateClick(dateStr) {
        let clickedDate = new Date(dateStr);
        let day = clickedDate.getDay();
        let diff = clickedDate.getDate() - day + (day === 0 ? -6 : 1);
        currentWeekStartDate.setTime(new Date(clickedDate.setDate(diff)).setHours(0, 0, 0, 0));
        renderWeeklyCalendar(cachedConfirmedEvents, handleWeeklyEventClick);
    }

    function handleWeeklyEventClick(ev) {
        actionBindWeeklyEventClickForCreate(
            ev,
            (id) => { activeEventId = id; },
            loadAllEvents
        );
    }

    // Gắn sự kiện nút điền lịch & báo cáo khảo sát
    function bindPollActionListeners() {
        document.querySelectorAll('.fill-poll-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                activeEventId = this.getAttribute('data-id');
                let config = {};
                try {
                    config = JSON.parse(this.getAttribute('data-config'));
                } catch (err) {
                    console.error("Lỗi parse cấu hình poll:", err);
                }

                buildPollMatrix(config, 'w2mMatrixTable', false);

                try {
                    const data = await fetchMyLatestAvailabilityApi();
                    const savedSlots = data.available_slots || [];
                    if (savedSlots.length > 0) {
                        const table = document.getElementById('w2mMatrixTable');
                        savedSlots.forEach(slotISO => {
                            const cell = table.querySelector(`.time-slot-cell[data-slot="${slotISO}"]`);
                            if (cell) cell.classList.add('selected');
                        });
                    }
                } catch (e) {
                    console.log('Không thể tải lịch rảnh cũ.', e);
                }

                window.openModal('pollMatrixModal');
            });
        });

        document.querySelectorAll('.view-report-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                activeEventId = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                let config = {};
                try {
                    config = JSON.parse(this.getAttribute('data-config'));
                } catch (err) {
                    console.error("Lỗi parse config:", err);
                }

                const reportTitle = document.getElementById('reportTitle');
                if (reportTitle) reportTitle.innerText = `Báo Cáo: ${title}`;

                try {
                    const json = await fetchPollReportApi(activeEventId);
                    document.getElementById('statTargetCount').innerText = `Tổng mục tiêu: ${json.target_count || 0}`;
                    document.getElementById('statSubmittedCount').innerText = `Đã phản hồi: ${json.submitted_count || 0}`;

                    buildHeatmapMatrix(config, json.slot_statistics || {}, json.target_count || 0);
                    window.openModal('reportModal');
                } catch (e) {
                    console.error(e);
                    alert('Không thể tải báo cáo khảo sát.');
                }
            });
        });
    }

    // Submit lưu lịch rảnh cá nhân
    document.getElementById('saveAvailabilityBtn')?.addEventListener('click', async function () {
        const selectedCells = document.querySelectorAll('#w2mMatrixTable .time-slot-cell.selected');
        const availableSlots = Array.from(selectedCells).map(c => c.getAttribute('data-slot'));

        try {
            await saveAvailabilityApi(activeEventId, availableSlots);
            alert('Đã gửi lịch rảnh thành công!');
            window.closeModal('pollMatrixModal');
            loadAllEvents();
        } catch (e) {
            console.error(e);
            alert(e.message || 'Lỗi kết nối máy chủ.');
        }
    });

    // Chốt lịch từ bảng báo cáo khảo sát
    document.getElementById('confirmPollBtn')?.addEventListener('click', async function () {
        if (!activeEventId) {
            alert('Không tìm thấy ID sự kiện.');
            return;
        }

        const selectedCells = document.querySelectorAll('#reportMatrixTable .time-slot-cell.selected');
        if (selectedCells.length === 0) {
            alert('Vui lòng bôi đen chọn khung giờ chốt trên bảng báo cáo.');
            return;
        }

        let slots = Array.from(selectedCells).map(c => c.getAttribute('data-slot')).sort();
        let startSlotStr = slots[0];
        let lastSlotStr = slots[slots.length - 1];

        let lastDate = new Date(lastSlotStr);
        lastDate.setMinutes(lastDate.getMinutes() + 30);

        const pad = (n) => String(n).padStart(2, '0');
        const formatDateTimeStr = (dateObj) => `${dateObj.getFullYear()}-${pad(dateObj.getMonth() + 1)}-${pad(dateObj.getDate())} ${pad(dateObj.getHours())}:${pad(dateObj.getMinutes())}:${pad(dateObj.getSeconds())}`;

        try {
            await confirmPollApi(activeEventId, startSlotStr.replace('T', ' '), formatDateTimeStr(lastDate));
            alert('Đã chốt lịch thành công!');
            window.closeModal('reportModal');
            loadAllEvents();
        } catch (e) {
            console.error(e);
            alert(e.message || 'Lỗi kết nối máy chủ.');
        }
    });
});
