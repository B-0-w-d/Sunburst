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
            bindPollActionListeners();
        } catch (err) {
            console.error('Lỗi tải danh sách lịch:', err);
        }
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
