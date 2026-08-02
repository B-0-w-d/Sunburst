// =========================================================================
// QUẢN LÝ SỰ KIỆN, GỬI DỮ LIỆU, LỊCH THÁNG VÀ LỊCH TUẦN
// =========================================================================

import { headers, formatEventDateTime } from './utils.js';
import { buildPollMatrix, buildHeatmapMatrix } from './poll.js';
import { removeMemberChip } from './addMember.js';

document.addEventListener('DOMContentLoaded', function () {
    const confirmedContainer = document.getElementById('confirmedEventList');
    const pollContainer = document.getElementById('pollEventList');

    let activeEventId = null;
    let cachedConfirmedEvents = [];
    let currentWeekStartDate = new Date();
    let dayOfWeek = currentWeekStartDate.getDay();
    let diffToMonday = currentWeekStartDate.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
    currentWeekStartDate.setDate(diffToMonday);
    currentWeekStartDate.setHours(0, 0, 0, 0);

    let currentMiniMonthDate = new Date();

    fetchAllEvents();

    document.getElementById('openCreateModalBtn')?.addEventListener('click', () => window.openModal('createEventModal'));

    // Nút chọn tất cả thành viên
    document.getElementById('selectAllMembersBtn')?.addEventListener('click', function () {
        const availableZone = document.getElementById('availableMembersZone');
        const selectedZone = document.getElementById('selectedMembersZone');

        if (!availableZone || !selectedZone) return;

        const availableChips = availableZone.querySelectorAll('.member-chip');
        availableChips.forEach(chip => {
            if (!chip.querySelector('.btn-remove-chip')) {
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn-remove-chip';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = function() { removeMemberChip(this); };
                chip.appendChild(removeBtn);
            }
            selectedZone.appendChild(chip);
        });

        const selectedZoneChips = selectedZone.querySelectorAll('.member-chip');
        const ids = Array.from(selectedZoneChips).map(c => c.dataset.id);
        const hiddenInput = document.getElementById('targetMemberIds');
        if (hiddenInput) hiddenInput.value = ids.join(',');
    });

    const eventStatusSelect = document.getElementById('eventStatus');
    if (eventStatusSelect) {
        eventStatusSelect.addEventListener('change', function () {
            const isPoll = this.value === 'POLL';
            const pollSec = document.getElementById('pollConfigSection');
            const confSec = document.getElementById('confirmedConfigSection');
            if (pollSec) pollSec.style.display = isPoll ? 'block' : 'none';
            if (confSec) confSec.style.display = isPoll ? 'none' : 'block';
        });
    }

    const allDayCheckbox = document.getElementById('allDayEventCheckbox');
    const startTimeInput = document.getElementById('startTime');
    const endTimeInput = document.getElementById('endTime');

    if (allDayCheckbox && startTimeInput && endTimeInput) {
        allDayCheckbox.addEventListener('change', function () {
            if (this.checked) {
                startTimeInput.type = 'date';
                endTimeInput.type = 'date';
                const today = new Date().toISOString().split('T')[0];
                startTimeInput.value = startTimeInput.value ? startTimeInput.value.split('T')[0] : today;
                endTimeInput.value = startTimeInput.value; // Tự động đồng bộ kết thúc bằng bắt đầu khi tick Cả ngày
            } else {
                startTimeInput.type = 'datetime-local';
                endTimeInput.type = 'datetime-local';
                startTimeInput.value = '';
                endTimeInput.value = '';
            }
        });

        // Tự động kéo / đồng bộ thời gian kết thúc nếu nó trống hoặc sớm hơn thời gian bắt đầu
        startTimeInput.addEventListener('change', function () {
            if (!this.value) return;
            if (!endTimeInput.value || endTimeInput.value < this.value) {
                endTimeInput.value = this.value;
            }
        });

        endTimeInput.addEventListener('change', function () {
            if (!this.value) return;
            if (startTimeInput.value && this.value < startTimeInput.value) {
                alert('Thời gian kết thúc không được sớm hơn thời gian bắt đầu. Hệ thống đã tự động điều chỉnh lại.');
                endTimeInput.value = startTimeInput.value;
            }
        });
    }

    const enableNotificationCheckbox = document.getElementById('enableNotification');
    const notifSettingsGroup = document.getElementById('notificationSettingsGroup');
    if (enableNotificationCheckbox && notifSettingsGroup) {
        enableNotificationCheckbox.addEventListener('change', function() {
            notifSettingsGroup.style.display = this.checked ? 'block' : 'none';
        });
    }

    async function fetchAllEvents() {
        try {
            const [resConfirmed, resPoll] = await Promise.all([
                fetch('/api/calendar?status=CONFIRMED', { headers: headers }),
                fetch('/api/calendar?status=POLL', { headers: headers })
            ]);

            const resultConfirmed = await resConfirmed.json();
            const resultPoll = await resPoll.json();

            if (confirmedContainer) renderEventList(confirmedContainer, resultConfirmed.data || [], 'confirmed');
            if (pollContainer) renderEventList(pollContainer, resultPoll.data || [], 'poll');

            cachedConfirmedEvents = resultConfirmed.data || [];
            renderUpcomingEvents(cachedConfirmedEvents);
            renderWeeklyCalendar();
            renderMiniCalendar();
            bindEventListeners();
            bindDeleteEventListeners();
        } catch (err) {
            console.error('Lỗi tải danh sách lịch:', err);
        }
    }

    function renderEventList(targetContainer, data, type) {
        targetContainer.innerHTML = '';
        if (!data || data.length === 0) {
            targetContainer.innerHTML = `<p class="subtitle" style="color: #6c757d; font-style: italic;">Không có ${type === 'confirmed' ? 'lịch đã chốt' : 'khảo sát'} nào.</p>`;
            return;
        }

        data.forEach(ev => {
            const card = document.createElement('div');
            card.className = 'event-card';
            card.style.position = 'relative';

            let eventId = ev._id || ev.id || (ev._id && ev._id.$oid);
            let actionBtn = '';
            let pollConfigStr = ev.poll_config ? JSON.stringify(ev.poll_config).replace(/'/g, "&apos;") : '{}';

            if (ev.status === 'POLL') {
                if (ev.is_manager) {
                    actionBtn = `<button class="btn btn-primary w-100 fill-poll-btn" data-id="${eventId}" data-config='${pollConfigStr}'>Điền Lịch Rảnh</button>`;
                    actionBtn += `<button class="btn btn-outline w-100 mt-2 view-report-btn" data-id="${eventId}" data-title="${ev.title}" data-config='${pollConfigStr}'>Xem Báo Cáo & Chốt Lịch</button>`;
                } else {
                    if (ev.has_submitted_availability) {
                        actionBtn = `<button class="btn w-100 fill-poll-btn" data-id="${eventId}" data-config='${pollConfigStr}' style="background-color: #28a745; color: white;">✓ Đã điền lịch rảnh (Sửa lại)</button>`;
                    } else {
                        actionBtn = `<button class="btn btn-primary w-100 fill-poll-btn" data-id="${eventId}" data-config='${pollConfigStr}'>Điền Lịch Rảnh</button>`;
                    }
                }
            }

            let deleteBtnHtml = ev.is_manager ? `<button class="delete-event-btn" data-id="${eventId}" title="Xóa sự kiện" style="position: absolute; top: 12px; right: 12px; background: transparent; border: none; font-size: 20px; font-weight: bold; color: #dc3545; cursor: pointer; line-height: 1; padding: 0 5px;">&times;</button>` : '';
            let timeDisplayHtml = ev.start_time ? `<p><strong>Thời gian:</strong> ${formatEventDateTime(ev.start_time, ev.end_time)}</p>` : '';

            card.innerHTML = `
                ${deleteBtnHtml}
                <div>
                    <span class="badge badge-${(ev.type || '').toLowerCase()}">${ev.type || 'N/A'}</span>
                    <h4>${ev.title}</h4>
                    <div class="event-info">
                        <p><strong>Trạng thái:</strong> ${ev.status}</p>
                        ${timeDisplayHtml}
                    </div>
                </div>
                <div style="margin-top: 15px;">${actionBtn}</div>
            `;
            targetContainer.appendChild(card);
        });
    }

    function bindEventListeners() {
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
                    const res = await fetch(`/api/calendar/my-latest-availability`, { headers: headers });
                    if (res.ok) {
                        const data = await res.json();
                        const savedSlots = data.available_slots || [];
                        if (savedSlots.length > 0) {
                            const table = document.getElementById('w2mMatrixTable');
                            savedSlots.forEach(slotISO => {
                                const cell = table.querySelector(`.time-slot-cell[data-slot="${slotISO}"]`);
                                if (cell) cell.classList.add('selected');
                            });
                        }
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
                    const res = await fetch(`/api/calendar/${activeEventId}/poll-report`, { headers: headers });
                    const json = await res.json();

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

    function bindDeleteEventListeners() {
        document.querySelectorAll('.delete-event-btn').forEach(btn => {
            if (btn.dataset.deleteListenerAttached) return;
            btn.dataset.deleteListenerAttached = "true";

            btn.addEventListener('click', async function () {
                const eventId = this.getAttribute('data-id');
                if (!confirm('Bạn có chắc chắn muốn xóa mục này không?')) return;

                try {
                    const res = await fetch(`/api/calendar/${eventId}`, { method: 'DELETE', headers: headers });
                    const result = await res.json();
                    if (res.ok) {
                        alert('Đã xóa thành công!');
                        fetchAllEvents();
                    } else {
                        alert(result.message || 'Không thể xóa.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Lỗi kết nối máy chủ.');
                }
            });
        });
    }

    document.getElementById('saveAvailabilityBtn')?.addEventListener('click', async function () {
        const selectedCells = document.querySelectorAll('#w2mMatrixTable .time-slot-cell.selected');
        const availableSlots = Array.from(selectedCells).map(c => c.getAttribute('data-slot'));

        try {
            const res = await fetch(`/api/calendar/${activeEventId}/availability`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ available_slots: availableSlots })
            });
            const result = await res.json();
            if (res.ok) {
                alert('Đã gửi lịch rảnh thành công!');
                window.closeModal('pollMatrixModal');
                fetchAllEvents();
            } else {
                alert(result.message || 'Lỗi gửi lịch rảnh.');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối máy chủ.');
        }
    });

    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm && !createEventForm.dataset.listenerAttached) {
        createEventForm.dataset.listenerAttached = "true";
        let isCreating = false;

        createEventForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (isCreating) return;
            isCreating = true;

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const statusVal = document.getElementById('eventStatus').value;
            const targetMembersVal = document.getElementById('targetMemberIds')?.value || '';
            const selectedRemindCheckboxes = document.querySelectorAll('input[name="remindMinutes"]:checked');
            const remindMinutesArray = Array.from(selectedRemindCheckboxes).map(cb => parseInt(cb.value, 10));

            let payload = {
                title: document.getElementById('eventTitle').value,
                type: document.getElementById('eventType').value,
                status: statusVal,
                target_member_ids: targetMembersVal.split(',').map(s => s.trim()).filter(Boolean),
                notification_settings: {
                    enabled: remindMinutesArray.length > 0,
                    remind_before_minutes: remindMinutesArray
                }
            };

            if (statusVal === 'POLL') {
                payload.poll_config = {
                    start_date: document.getElementById('pollStartDate').value,
                    end_date: document.getElementById('pollEndDate').value,
                    daily_start_time: document.getElementById('dailyStartTime').value,
                    daily_end_time: document.getElementById('dailyEndTime').value,
                    step_minutes: 30
                };
            } else {
                let startVal = startTimeInput.value;
                let endVal = endTimeInput.value;

                if (allDayCheckbox && allDayCheckbox.checked) {
                    if (!endVal || endVal < startVal) {
                        endVal = startVal;
                    }
                    startVal = `${startVal} 00:00:00`;
                    endVal = `${endVal} 23:59:59`;
                }

                payload.start_time = startVal;
                payload.end_time = endVal;
            }

            try {
                const res = await fetch('/api/calendar', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (res.ok) {
                    alert('Tạo thành công!');
                    window.closeModal('createEventModal');
                    createEventForm.reset();
                    fetchAllEvents();
                } else {
                    alert(result.message || 'Lỗi tạo sự kiện.');
                }
            } catch (ex) {
                console.error(ex);
                alert('Lỗi kết nối máy chủ.');
            } finally {
                isCreating = false;
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

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

        let slots = [];
        selectedCells.forEach(cell => {
            let slot = cell.getAttribute('data-slot');
            if (slot) slots.push(slot);
        });

        slots.sort();
        let startSlotStr = slots[0];
        let lastSlotStr = slots[slots.length - 1];

        let lastDate = new Date(lastSlotStr);
        lastDate.setMinutes(lastDate.getMinutes() + 30);

        const pad = (n) => String(n).padStart(2, '0');
        const formatDateTimeStr = (dateObj) => {
            return `${dateObj.getFullYear()}-${pad(dateObj.getMonth() + 1)}-${pad(dateObj.getDate())} ${pad(dateObj.getHours())}:${pad(dateObj.getMinutes())}:${pad(dateObj.getSeconds())}`;
        };

        let startTimeFormatted = startSlotStr.replace('T', ' ');
        let endTimeFormatted = formatDateTimeStr(lastDate);

        try {
            const res = await fetch(`/api/calendar/${activeEventId}/confirm-poll`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ start_time: startTimeFormatted, end_time: endTimeFormatted })
            });
            const result = await res.json();
            if (res.ok) {
                alert('Đã chốt lịch thành công!');
                window.closeModal('reportModal');
                fetchAllEvents();
            } else {
                alert(result.message || 'Lỗi chốt lịch.');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối máy chủ.');
        }
    });

    function renderMiniCalendar() {
            const grid = document.getElementById('miniCalendarGrid');
            const label = document.getElementById('miniCalendarMonthYear');
            if (!grid || !label) return;

            grid.innerHTML = '';
            let year = currentMiniMonthDate.getFullYear();
            let month = currentMiniMonthDate.getMonth();
            label.innerText = `Tháng ${month + 1}, ${year}`;

            let firstDayIndex = new Date(year, month, 1).getDay();
            firstDayIndex = firstDayIndex === 0 ? 6 : firstDayIndex - 1;
            let totalDays = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDayIndex; i++) {
                grid.innerHTML += `<span></span>`;
            }

            let todayStr = new Date().toISOString().split('T')[0];

            // Định nghĩa bảng màu cho từng loại sự kiện
            const typeColors = {
                'PRACTICE': '#10b981', // Xanh lá
                'SHOW': '#ef4444',     // Đỏ
                'MEETING': '#f59e0b'   // Cam
            };

            for (let d = 1; d <= totalDays; d++) {
                let dateObj = new Date(year, month, d);
                let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                let isToday = dateStr === todayStr;

                // Tìm sự kiện diễn ra trong ngày này
                let dayEvent = cachedConfirmedEvents.find(ev => {
                    if (!ev.start_time) return false;
                    const cleanStart = ev.start_time.replace(/\.\d+([+-]\d{2}:\d{2}|Z)?$/, '').replace('T', ' ');
                    return cleanStart.startsWith(dateStr);
                });

                let style = `padding: 4px 0; text-align: center; border-radius: 4px; cursor: pointer; position: relative; font-size: 12px;`;

                if (isToday) {
                    style += ` background: #3b82f6; color: #fff; font-weight: bold;`;
                } else if (dayEvent) {
                    // Tô màu theo type event, nếu không khớp type nào thì mặc định màu xanh dương nhạt
                    let bgColor = typeColors[dayEvent.type] || '#3b82f6';
                    style += ` background: ${bgColor}; color: #fff; font-weight: bold;`;
                } else {
                    style += ` color: #334155;`;
                }

                let cell = document.createElement('div');
                cell.className = 'mini-day-cell';
                cell.style.cssText = style;
                cell.innerHTML = `${d}`;
                cell.dataset.date = dateStr;
                grid.appendChild(cell);
            }

            grid.querySelectorAll('.mini-day-cell').forEach(cell => {
                cell.addEventListener('click', function() {
                    let clickedDate = new Date(this.getAttribute('data-date'));
                    let day = clickedDate.getDay();
                    let diff = clickedDate.getDate() - day + (day === 0 ? -6 : 1);
                    currentWeekStartDate = new Date(clickedDate.setDate(diff));
                    currentWeekStartDate.setHours(0, 0, 0, 0);
                    renderWeeklyCalendar();
                });
            });
        }

    document.getElementById('miniPrevBtn')?.addEventListener('click', () => {
        currentMiniMonthDate.setMonth(currentMiniMonthDate.getMonth() - 1);
        renderMiniCalendar();
    });
    document.getElementById('miniNextBtn')?.addEventListener('click', () => {
        currentMiniMonthDate.setMonth(currentMiniMonthDate.getMonth() + 1);
        renderMiniCalendar();
    });

    function renderWeeklyCalendar() {
        const grid = document.getElementById('weeklyCalendarGrid');
        const titleLabel = document.getElementById('currentWeekTitle');
        if (!grid) return;

        grid.innerHTML = '';
        let weekDays = [];
        let tempDate = new Date(currentWeekStartDate);
        for (let i = 0; i < 7; i++) {
            weekDays.push(new Date(tempDate));
            tempDate.setDate(tempDate.getDate() + 1);
        }

        let startStr = `${weekDays[0].getDate()}/${weekDays[0].getMonth() + 1}`;
        let endStr = `${weekDays[6].getDate()}/${weekDays[6].getMonth() + 1}/${weekDays[6].getFullYear()}`;
        if (titleLabel) {
            titleLabel.innerHTML = `<span style="width: 10px; height: 10px; border-radius: 50%; background-color: #10b981; display: inline-block; margin-right: 6px;"></span> Lịch Đã Chốt (${startStr} - ${endStr})`;
        }

        let hours = [];
        for (let h = 0; h < 24; h++) hours.push(h);

        const rowHeight = 34;
        grid.style.display = 'grid';
        grid.style.gridTemplateColumns = '60px repeat(7, 1fr)';
        grid.style.gridTemplateRows = `45px repeat(24, ${rowHeight}px) 20px`;

        let headerTime = document.createElement('div');
        headerTime.style.cssText = `border-right: 1px solid #e2e8f0; background: #f8fafc; grid-row: 1; grid-column: 1;`;
        grid.appendChild(headerTime);

        weekDays.forEach((d, idx) => {
            let dayNames = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
            let isToday = new Date().toDateString() === d.toDateString();
            let headerDay = document.createElement('div');
            headerDay.style.cssText = `padding: 6px; text-align: center; border-right: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: ${isToday ? '#eff6ff' : '#f8fafc'}; grid-row: 1; grid-column: ${idx + 2};`;
            headerDay.innerHTML = `
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">${dayNames[idx]}</div>
                <div style="font-size: 14px; font-weight: 700; color: ${isToday ? '#2563eb' : '#1e293b'};">${d.getDate()}/${d.getMonth()+1}</div>
            `;
            grid.appendChild(headerDay);
        });

        let dayCellMatrix = [];
        hours.forEach((hour, hIndex) => {
            let rowIndex = hIndex + 2;
            let timeLabelText = `${String(hour).padStart(2, '0')}:00`;

            let timeLabelDiv = document.createElement('div');
            timeLabelDiv.style.cssText = `padding: 4px 6px; font-size: 11px; color: #94a3b8; text-align: right; border-right: 1px solid #e2e8f0; border-top: 1px solid #f1f5f9; grid-row: ${rowIndex}; grid-column: 1;`;
            timeLabelDiv.innerText = timeLabelText;
            grid.appendChild(timeLabelDiv);

            weekDays.forEach((d, colIndex) => {
                let cellDiv = document.createElement('div');
                cellDiv.style.cssText = `border-right: 1px solid #e2e8f0; border-top: 1px solid #f1f5f9; grid-column: ${colIndex + 2}; grid-row: ${rowIndex}; position: relative; box-sizing: border-box; background: transparent;`;
                grid.appendChild(cellDiv);

                if (!dayCellMatrix[colIndex]) dayCellMatrix[colIndex] = [];
                dayCellMatrix[colIndex][hour] = cellDiv;
            });
        });

        let bottomRowIndex = hours.length + 2;
        let footerTimeDiv = document.createElement('div');
        footerTimeDiv.style.cssText = `padding: 2px 6px; font-size: 11px; color: #94a3b8; text-align: right; border-right: 1px solid #e2e8f0; border-top: 1px solid #f1f5f9; grid-row: ${bottomRowIndex}; grid-column: 1;`;
        footerTimeDiv.innerText = '24:00';
        grid.appendChild(footerTimeDiv);

        weekDays.forEach((d, colIndex) => {
            let footerCellDiv = document.createElement('div');
            footerCellDiv.style.cssText = `border-right: 1px solid #e2e8f0; border-top: 1px solid #f1f5f9; grid-column: ${colIndex + 2}; grid-row: ${bottomRowIndex}; background: transparent;`;
            grid.appendChild(footerCellDiv);
        });

        const parseLocalDateTime = (dateStr) => {
            if (!dateStr) return new Date();
            const cleanStr = dateStr.replace('Z', '').replace('T', ' ');
            const parts = cleanStr.split(' ');
            const datePart = parts[0];
            const timePart = parts[1] || '00:00:00';

            const [y, m, d] = datePart.split('-').map(Number);
            const [h, min, s] = timePart.split(':').map(Number);
            return new Date(y, m - 1, d, h || 0, min || 0, s || 0);
        };

        cachedConfirmedEvents.forEach(ev => {
            if (!ev.start_time) return;
            let evStart = parseLocalDateTime(ev.start_time);
            let evEnd = ev.end_time ? parseLocalDateTime(ev.end_time) : new Date(evStart.getTime() + 60 * 60 * 1000);

            let isAllDay = ev.start_time.includes('00:00:00') && ev.end_time && (ev.end_time.includes('23:59:59') || ev.end_time.includes('24:00:00'));
            let timeTextDisplay = isAllDay ? 'Cả ngày' : formatEventDateTime(ev.start_time, ev.end_time);

            weekDays.forEach((d, colIndex) => {
                let dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                let dayStart = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0, 0);
                let dayEnd = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59, 999);

                if (evEnd >= dayStart && evStart <= dayEnd) {
                    let actualStart = evStart < dayStart ? dayStart : evStart;
                    let actualEnd = evEnd > dayEnd ? dayEnd : evEnd;

                    let startHour = actualStart.getHours();
                    let startMinute = actualStart.getMinutes();
                    let endTotalMinutes = actualEnd.getHours() * 60 + actualEnd.getMinutes();

                    let evStartDateStr = `${evStart.getFullYear()}-${String(evStart.getMonth() + 1).padStart(2, '0')}-${String(evStart.getDate()).padStart(2, '0')}`;
                    if (evEnd > dayEnd && dateStr === evStartDateStr) {
                        endTotalMinutes = 24 * 60;
                    }
                    let startTotalMinutes = startHour * 60 + startMinute;
                    let durationMinutes = endTotalMinutes - startTotalMinutes;
                    if (durationMinutes < 15) durationMinutes = 15;

                    let targetCell = dayCellMatrix[colIndex] ? dayCellMatrix[colIndex][startHour] : null;
                    if (!targetCell) return;

                    let topPx = (startMinute / 60) * rowHeight;
                    let heightPx = (durationMinutes / 60) * rowHeight - 2;

                    let eventCard = document.createElement('div');
                    eventCard.style.cssText = `position: absolute; top: ${topPx}px; left: 2px; right: 2px; height: ${heightPx}px; background: #e0f2fe; border-left: 3px solid #0284c7; padding: 4px 6px; border-radius: 4px; font-size: 11px; overflow: hidden; z-index: 5; box-shadow: 0 1px 2px rgba(0,0,0,0.05); box-sizing: border-box;`;
                    eventCard.innerHTML = `
                        <strong style="color: #0369a1; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${ev.title}</strong>
                        <span style="color: #64748b; font-size: 10px;">${timeTextDisplay}</span>
                    `;
                    targetCell.appendChild(eventCard);
                }
            });
        });
    }

    function renderUpcomingEvents(allEvents) {
            const container = document.getElementById('upcomingEventsList');
            if (!container) return;

            const now = new Date();

            const processedEvents = allEvents.map(event => {
                // Lấy chuỗi và cắt bỏ phần múi giờ (+00:00 hoặc Z) để giữ nguyên vẹn giờ gốc
                const rawStart = event.start_time || event.start;
                const cleanStartStr = rawStart ? rawStart.replace(/\.\d+([+-]\d{2}:\d{2}|Z)?$/, '').replace('T', ' ') : new Date();
                const start = new Date(cleanStartStr.replace(' ', 'T'));

                const rawEnd = event.end_time;
                const cleanEndStr = rawEnd ? rawEnd.replace(/\.\d+([+-]\d{2}:\d{2}|Z)?$/, '').replace('T', ' ') : null;
                const end = cleanEndStr ? new Date(cleanEndStr.replace(' ', 'T')) : new Date(start.getTime() + 60 * 60 * 1000);

                let statusType = 'upcoming';
                if (now >= start && now <= end) {
                    statusType = 'ongoing';
                } else if (now > end) {
                    statusType = 'passed';
                }
                return { ...event, startDate: start, endDate: end, statusType };
            });

            const relevantEvents = processedEvents.filter(event => event.statusType !== 'passed');

            relevantEvents.sort((a, b) => {
                if (a.statusType === 'ongoing' && b.statusType !== 'ongoing') return -1;
                if (a.statusType !== 'ongoing' && b.statusType === 'ongoing') return 1;
                return a.startDate - b.startDate;
            });

            if (relevantEvents.length === 0) {
                container.innerHTML = `<p style="font-size: 12px; color: #888; padding: 4px 8px;">Không có sự kiện nào.</p>`;
                return;
            }

            container.innerHTML = relevantEvents.map(event => {
                const d = event.startDate;
                const hours = String(d.getHours()).padStart(2, '0');
                const minutes = String(d.getMinutes()).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                const formattedDate = `${hours}:${minutes} - ${day}/${month}/${year}`;

                let dotColor = '#3b82f6';
                if (event.type === 'PRACTICE') dotColor = '#10b981';
                else if (event.type === 'SHOW') dotColor = '#ef4444';
                else if (event.type === 'MEETING') dotColor = '#f59e0b';

                const eventId = event.id || event.event_id || event._id;

                const badgeHtml = event.statusType === 'ongoing'
                    ? `<span style="font-size: 10px; background: #fee2e2; color: #dc2626; padding: 1px 6px; border-radius: 4px; font-weight: 600; margin-bottom: 2px;">Đang diễn ra</span>`
                    : '';

                return `
                    <div class="sidebar-nav-item" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; background: ${event.statusType === 'ongoing' ? '#fff5f5' : '#f9fafb'}; border: ${event.statusType === 'ongoing' ? '1px solid #fecaca' : 'none'}; border-radius: 6px; text-decoration: none; color: inherit; position: relative;">
                        <a href="#" style="display: flex; flex-direction: column; align-items: flex-start; text-decoration: none; color: inherit; flex-grow: 1; overflow: hidden; margin-right: 8px;">
                            ${badgeHtml}
                            <div style="display: flex; align-items: center; gap: 6px; width: 100%;">
                                <span class="nav-dot" style="background-color: ${dotColor}; flex-shrink: 0; width: 8px; height: 8px; border-radius: 50%;"></span>
                                <span style="font-weight: 500; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${event.title}</span>
                            </div>
                            <span style="font-size: 11px; color: #6b7280; margin-left: 14px; margin-top: 2px;">${formattedDate}</span>
                        </a>
                        <button class="delete-event-btn" data-id="${eventId}" title="Xóa sự kiện" style="background: none; border: none; color: #9ca3af; font-size: 16px; cursor: pointer; padding: 0 4px; line-height: 1; border-radius: 4px;" onmouseover="this.style.color='#ef4444'; this.style.backgroundColor='#fee2e2';" onmouseout="this.style.color='#9ca3af'; this.style.backgroundColor='transparent';">&times;</button>
                    </div>
                `;
            }).join('');

            bindDeleteEventListeners();
        }

    document.getElementById('weekPrevBtn')?.addEventListener('click', () => {
        currentWeekStartDate.setDate(currentWeekStartDate.getDate() - 7);
        renderWeeklyCalendar();
    });

    document.getElementById('weekNextBtn')?.addEventListener('click', () => {
        currentWeekStartDate.setDate(currentWeekStartDate.getDate() + 7);
        renderWeeklyCalendar();
    });

    document.getElementById('weekTodayBtn')?.addEventListener('click', () => {
        currentWeekStartDate = new Date();
        let day = currentWeekStartDate.getDay();
        let diff = currentWeekStartDate.getDate() - day + (day === 0 ? -6 : 1);
        currentWeekStartDate.setDate(diff);
        currentWeekStartDate.setHours(0, 0, 0, 0);
        renderWeeklyCalendar();
    });
});
