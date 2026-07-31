// =========================================================================
// HÀM TIỆN ÍCH DÙNG CHUNG (GLOBAL UTILITY)
// =========================================================================

/**
 * Định dạng chuỗi thời gian ISO thành định dạng hiển thị dễ đọc (HH:mm - DD/MM/YYYY)
 * @param {string} isoString - Chuỗi thời gian định dạng ISO
 * @returns {string} - Thời gian sau khi định dạng hoặc chuỗi gốc nếu lỗi
 */
function formatDateTime(isoString) {
    if (!isoString) return '';
    try {
        const date = new Date(isoString);
        if (isNaN(date.getTime())) return isoString;

        const pad = (n) => String(n).padStart(2, '0');
        const hours = pad(date.getHours());
        const minutes = pad(date.getMinutes());
        const day = pad(date.getDate());
        const month = pad(date.getMonth() + 1);
        const year = date.getFullYear();

        return `${hours}:${minutes} - ${day}/${month}/${year}`;
    } catch (e) {
        return isoString;
    }
}

// =========================================================================
// CẤU HÌNH BAN ĐẦU & XÁC THỰC API
// =========================================================================

// Lấy token xác thực từ localStorage để gắn vào các request API
const token = localStorage.getItem('access_token');
const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
};

// =========================================================================
// QUẢN LÝ KÉO THẢ THÀNH VIÊN (DRAG & DROP MEMBERS) - GLOBAL SCOPE
// =========================================================================
let draggedMemberElement = null;

/**
 * Xử lý sự kiện khi bắt đầu kéo (drag start) một thành viên
 */
window.handleMemberDragStart = function(e) {
    draggedMemberElement = e.currentTarget;
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.id);
};

/**
 * Xử lý sự kiện khi thả (drop) thành viên vào vùng mục tiêu (đã chọn hoặc khả dụng)
 */
window.handleMemberDrop = function(e, targetZoneType) {
    e.preventDefault();
    if (!draggedMemberElement) return;

    const targetZone = targetZoneType === 'selected'
        ? document.getElementById('selectedMembersZone')
        : document.getElementById('availableMembersZone');

    if (!targetZone) return;

    // Thêm nút xóa (chip) nếu chuyển vào vùng 'selected', ngược lại thì gỡ bỏ
    if (targetZoneType === 'selected' && !draggedMemberElement.querySelector('.btn-remove-chip')) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-remove-chip';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function() { window.removeMemberChip(this); };
        draggedMemberElement.appendChild(removeBtn);
    } else if (targetZoneType === 'available') {
        const btn = draggedMemberElement.querySelector('.btn-remove-chip');
        if (btn) btn.remove();
    }

    targetZone.appendChild(draggedMemberElement);
    window.updateMemberHiddenInput();
    draggedMemberElement = null;
};

/**
 * Xóa một thành viên khỏi danh sách đã chọn thông qua nút "x" trên chip
 */
window.removeMemberChip = function(btn) {
    const chip = btn.closest('.member-chip');
    const availableZone = document.getElementById('availableMembersZone');
    if (chip && availableZone) {
        btn.remove();
        availableZone.appendChild(chip);
        window.updateMemberHiddenInput();
    }
};

/**
 * Cập nhật giá trị ID của các thành viên được chọn vào thẻ input ẩn để gửi form
 */
window.updateMemberHiddenInput = function() {
    const selectedZone = document.getElementById('selectedMembersZone');
    if (!selectedZone) return;
    const chips = selectedZone.querySelectorAll('.member-chip');
    const ids = Array.from(chips).map(chip => chip.dataset.id);

    const hiddenInput = document.getElementById('targetMemberIds');
    if (hiddenInput) {
        hiddenInput.value = ids.join(',');
    }
};

// =========================================================================
// KHỞI TẠO DOM CONTENT LOADED
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    const confirmedContainer = document.getElementById('confirmedEventList');
    const pollContainer = document.getElementById('pollEventList');

    let activeEventId = null;
    let isMouseDown = false;
    let isSelecting = true;

    // Tải toàn bộ danh sách sự kiện và khảo sát ngay khi trang được tải
    fetchAllEvents();

    // ---------------------------------------------------------------------
    // Quản lý Bật/Tắt Modal (Popup)
    // ---------------------------------------------------------------------
    function toggleModal(modalId, show = true) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (show) {
                modal.classList.add('active');
                modal.style.display = 'flex'; // Đảm bảo hiển thị đè lên CSS ẩn mặc định
            } else {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        }
    }

    // Sự kiện mở modal tạo sự kiện mới
    document.getElementById('openCreateModalBtn')?.addEventListener('click', () => toggleModal('createEventModal', true));

    // Sự kiện đóng modal qua nút có class 'close-btn'
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal');
            toggleModal(modalId, false);
        });
    });

    // ---------------------------------------------------------------------
    // Xử lý Sự kiện Nút "Chọn tất cả thành viên"
    // ---------------------------------------------------------------------
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
                removeBtn.onclick = function() { window.removeMemberChip(this); };
                chip.appendChild(removeBtn);
            }
            selectedZone.appendChild(chip);
        });
        window.updateMemberHiddenInput();
    });

    // ---------------------------------------------------------------------
    // Thay đổi cấu hình Form theo Trạng thái (POLL / CONFIRMED)
    // ---------------------------------------------------------------------
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

    // ---------------------------------------------------------------------
    // Checkbox chọn "Cả ngày" (All-day event)
    // ---------------------------------------------------------------------
    const allDayCheckbox = document.getElementById('allDayEventCheckbox');
    if (allDayCheckbox) {
        allDayCheckbox.addEventListener('change', function () {
            const startTimeInput = document.getElementById('startTime');
            const endTimeInput = document.getElementById('endTime');

            if (!startTimeInput || !endTimeInput) return;

            if (this.checked) {
                startTimeInput.type = 'date';
                endTimeInput.type = 'date';

                const today = new Date().toISOString().split('T')[0];
                let startVal = startTimeInput.value ? startTimeInput.value.split('T')[0] : today;
                let endVal = endTimeInput.value ? endTimeInput.value.split('T')[0] : startVal;

                startTimeInput.value = startVal;
                endTimeInput.value = endVal;
            } else {
                startTimeInput.type = 'datetime-local';
                endTimeInput.type = 'datetime-local';

                startTimeInput.value = '';
                endTimeInput.value = '';
            }
        });
    }

    // ---------------------------------------------------------------------
    // Bật/Tắt nhóm cài đặt thông báo nhắc nhở (Tích hợp notificationController)
    // ---------------------------------------------------------------------
    const enableNotificationCheckbox = document.getElementById('enableNotification');
    const notifSettingsGroup = document.getElementById('notificationSettingsGroup');

    if (enableNotificationCheckbox && notifSettingsGroup) {
        enableNotificationCheckbox.addEventListener('change', function() {
            notifSettingsGroup.style.display = this.checked ? 'block' : 'none';
        });
    }

    // ---------------------------------------------------------------------
    // API 1: Tải danh sách Sự kiện đã chốt và Khảo sát từ Server
    // ---------------------------------------------------------------------
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

            bindEventListeners();
            bindDeleteEventListeners();
        } catch (err) {
            console.error('Lỗi tải danh sách lịch:', err);
        }
    }

    /**
     * Render danh sách sự kiện/khảo sát vào container tương ứng trên giao diện
     */
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

            let deleteBtnHtml = '';
            if (ev.is_manager) {
                deleteBtnHtml = `<button class="delete-event-btn" data-id="${eventId}" title="Xóa sự kiện" style="position: absolute; top: 12px; right: 12px; background: transparent; border: none; font-size: 20px; font-weight: bold; color: #dc3545; cursor: pointer; line-height: 1; padding: 0 5px;">&times;</button>`;
            }

            card.innerHTML = `
                ${deleteBtnHtml}
                <div>
                    <span class="badge badge-${(ev.type || '').toLowerCase()}">${ev.type || 'N/A'}</span>
                    <h4>${ev.title}</h4>
                    <div class="event-info">
                        <p><strong>Trạng thái:</strong> ${ev.status}</p>
                        ${ev.start_time ? `<p><strong>Bắt đầu:</strong> ${formatDateTime(ev.start_time)}</p>` : ''}
                    </div>
                </div>
                <div style="margin-top: 15px;">${actionBtn}</div>
            `;
            targetContainer.appendChild(card);
        });
    }

    /**
     * Gắn sự kiện click cho các nút Điền Lịch Rảnh và Xem Báo Cáo
     */
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
                    console.log('Không thể tải lịch rảnh cũ, hiển thị bảng trống.', e);
                }

                toggleModal('pollMatrixModal', true);
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
                    toggleModal('reportModal', true);
                } catch (e) {
                    console.error(e);
                    alert('Không thể tải báo cáo khảo sát.');
                }
            });
        });
    }

    /**
     * Gắn sự kiện xóa sự kiện cho quản lý
     */
    function bindDeleteEventListeners() {
        document.querySelectorAll('.delete-event-btn').forEach(btn => {
            if (btn.dataset.deleteListenerAttached) return;
            btn.dataset.deleteListenerAttached = "true";

            btn.addEventListener('click', async function () {
                const eventId = this.getAttribute('data-id');
                if (!confirm('Bạn có chắc chắn muốn xóa mục này không?')) return;

                try {
                    const res = await fetch(`/api/calendar/${eventId}`, {
                        method: 'DELETE',
                        headers: headers
                    });
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

    // ---------------------------------------------------------------------
    // 2. Dựng bảng ma trận khảo sát (W2M Matrix - When2Meet style)
    // ---------------------------------------------------------------------
    function buildPollMatrix(config, tableId, isReadonly = false, slotStats = null) {
        const table = document.getElementById(tableId);
        if (!table) return;
        table.innerHTML = '';

        const startDate = config.start_date ? new Date(config.start_date) : new Date();
        const endDate = config.end_date ? new Date(config.end_date) : new Date();
        const step = config.step_minutes || 30;

        let dates = [];
        let curr = new Date(startDate);
        while (curr <= endDate) {
            dates.push(new Date(curr));
            curr.setDate(curr.getDate() + 1);
        }

        let timeSlots = [];
        let totalMinutesStart = 6 * 60;
        let totalMinutesEnd = 24 * 60;
        for (let m = totalMinutesStart; m < totalMinutesEnd; m += step) {
            let hh = String(Math.floor(m / 60)).padStart(2, '0');
            let mm = String(m % 60).padStart(2, '0');
            timeSlots.push(`${hh}:${mm}`);
        }
        timeSlots.push("24:00");

        let headerRow1 = '<tr><th class="time-col-header" rowspan="2">Thời gian</th>';
        let headerRow2 = '<tr>';

        dates.forEach((d, index) => {
            let dayName = d.toLocaleDateString('vi-VN', { weekday: 'short' });
            let dayMonth = `${d.getDate()}/${d.getMonth() + 1}`;

            headerRow1 += `<th>${dayName}</th>`;

            if (!isReadonly) {
                headerRow2 += `<th>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                        <span>${dayMonth}</span>
                        <button type="button" class="select-col-btn" data-col-index="${index}" style="font-size: 11px; padding: 2px 6px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: #f8f9fa;">Chọn cả ngày</button>
                    </div>
                </th>`;
            } else {
                headerRow2 += `<th>${dayMonth}</th>`;
            }
        });
        headerRow1 += '</tr>';
        headerRow2 += '</tr>';
        table.innerHTML += headerRow1 + headerRow2;

        for (let i = 0; i < timeSlots.length; i++) {
            let time = timeSlots[i];
            let row = '<tr>';

            if (time === "24:00") {
                row += `<td class="time-label-cell"><span>12:00 AM</span></td>`;
            } else {
                let [hh, mm] = time.split(':').map(Number);
                let hour12 = hh === 0 ? 12 : (hh > 12 ? hh - 12 : hh);
                let period = hh >= 12 ? 'PM' : 'AM';
                let timeFormatted = `${hour12}:${String(mm).padStart(2, '0')} ${period}`;
                row += `<td class="time-label-cell"><span>${timeFormatted}</span></td>`;
            }

            dates.forEach((d, index) => {
                let dateStr = d.toISOString().split('T')[0];
                let slotISO = `${dateStr}T${time === "24:00" ? "00:00" : time}:00`;

                if (isReadonly) {
                    let count = slotStats ? (slotStats[slotISO] || 0) : 0;
                    let heatmapClass = count > 0 ? (count > 2 ? 'heatmap-high' : 'heatmap-mid') : '';
                    row += `<td class="time-slot-cell ${heatmapClass}" data-slot="${slotISO}">${count}</td>`;
                } else {
                    let borderClass = (time !== "24:00" && time.endsWith('30')) ? 'slot-half-hour' : 'slot-full-hour';
                    row += `<td class="time-slot-cell ${borderClass}" data-col-index="${index}" data-slot="${slotISO}"></td>`;
                }
            });

            row += '</tr>';
            table.innerHTML += row;
        }

        if (!isReadonly) {
            table.querySelectorAll('.select-col-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const colIndex = this.getAttribute('data-col-index');
                    const colCells = table.querySelectorAll(`.time-slot-cell[data-col-index="${colIndex}"]`);

                    const selectedCount = Array.from(colCells).filter(c => c.classList.contains('selected')).length;
                    const shouldSelect = selectedCount < colCells.length;

                    colCells.forEach(cell => cell.classList.toggle('selected', shouldSelect));

                    this.innerText = shouldSelect ? 'Bỏ chọn' : 'Chọn cả ngày';
                    this.style.background = shouldSelect ? '#007bff' : '#f8f9fa';
                    this.style.color = shouldSelect ? '#fff' : '#000';
                });
            });
        }

        initDragSelection(table);
    }

    // ---------------------------------------------------------------------
    // 3. Tính năng kéo chuột bôi đen/chọn các ô thời gian (Drag selection)
    // ---------------------------------------------------------------------
    function initDragSelection(table) {
        const cells = table.querySelectorAll('.time-slot-cell');
        cells.forEach(cell => {
            cell.addEventListener('mousedown', (e) => {
                isMouseDown = true;
                isSelecting = !cell.classList.contains('selected');
                cell.classList.toggle('selected', isSelecting);
                e.preventDefault();
            });

            cell.addEventListener('mouseenter', () => {
                if (isMouseDown) {
                    cell.classList.toggle('selected', isSelecting);
                }
            });
        });

        document.addEventListener('mouseup', () => {
            isMouseDown = false;
        });
    }

    function buildHeatmapMatrix(config, slotStats, targetCount) {
        buildPollMatrix(config, 'reportMatrixTable', true, slotStats);
    }

    // ---------------------------------------------------------------------
    // 4. Gửi lịch rảnh cá nhân lên máy chủ (Submit Availability)
    // ---------------------------------------------------------------------
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
                toggleModal('pollMatrixModal', false);
                fetchAllEvents();
            } else {
                alert(result.message || 'Lỗi gửi lịch rảnh.');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối máy chủ.');
        }
    });

    // ---------------------------------------------------------------------
    // 5. Submit Tạo Sự Kiện / Khảo Sát Mới (Đã tích hợp notification_settings)
    // ---------------------------------------------------------------------
    // 5. Submit Tạo Sự Kiện / Khảo Sát Mới (Đã hỗ trợ chọn nhiều nhắc nhở)
    // 5. Submit Tạo Sự Kiện / Khảo Sát Mới (Hỗ trợ danh sách nhắc nhở trực tiếp)
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

            // Thu thập tất cả các mốc thời gian được tích chọn vào một mảng (Array)
            const selectedRemindCheckboxes = document.querySelectorAll('input[name="remindMinutes"]:checked');
            const remindMinutesArray = Array.from(selectedRemindCheckboxes).map(cb => parseInt(cb.value, 10));

            let payload = {
                title: document.getElementById('eventTitle').value,
                type: document.getElementById('eventType').value,
                status: statusVal,
                target_member_ids: targetMembersVal.split(',').map(s => s.trim()).filter(Boolean),
                notification_settings: {
                    enabled: remindMinutesArray.length > 0, // Tự động bật nếu người dùng có chọn ít nhất 1 mốc
                    remind_before_minutes: remindMinutesArray // Mảng chứa các mốc thời gian
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
                payload.start_time = document.getElementById('startTime').value;
                payload.end_time = document.getElementById('endTime').value;
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
                    toggleModal('createEventModal', false);
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

    // ---------------------------------------------------------------------
    // 6. Chốt lịch chính thức từ Báo cáo Khảo sát (Confirm Poll)
    // ---------------------------------------------------------------------
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
            if (slot) slots.push(new Date(slot));
        });

        slots.sort((a, b) => a - b);

        let startTimeObj = slots[0];
        let endTimeObj = new Date(slots[slots.length - 1]);
        endTimeObj.setMinutes(endTimeObj.getMinutes() + 30);

        const formatDate = (date) => {
            let pad = (n) => String(n).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
        };

        try {
            const res = await fetch(`/api/calendar/${activeEventId}/confirm-poll`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ start_time: formatDate(startTimeObj), end_time: formatDate(endTimeObj) })
            });
            const result = await res.json();
            if (res.ok) {
                alert('Đã chốt lịch thành công!');
                toggleModal('reportModal', false);
                fetchAllEvents();
            } else {
                alert(result.message || 'Lỗi chốt lịch.');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối máy chủ.');
        }
    });
});
