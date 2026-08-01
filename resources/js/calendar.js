// =========================================================================
// HÀM TIỆN ÍCH DÙNG CHUNG (GLOBAL UTILITY)
// =========================================================================

/**
 * Định dạng khoảng thời gian hiển thị:
 * - Nếu cùng ngày: "HH:mm - HH:mm, DD/MM/YYYY"
 * - Nếu khác ngày: "HH:mm, DD/MM/YYYY - HH:mm, DD/MM/YYYY"
 * @param {string} startIso - Thời gian bắt đầu dạng ISO string
 * @param {string} endIso - Thời gian kết thúc dạng ISO string
 * @returns {string} Chuỗi thời gian đã được định dạng chuẩn tiếng Việt
 */
function formatEventDateTime(startIso, endIso) {
    if (!startIso) return '';
    try {
        const startDate = new Date(startIso);
        if (isNaN(startDate.getTime())) return startIso;

        const pad = (n) => String(n).padStart(2, '0');
        const startHours = pad(startDate.getHours());
        const startMinutes = pad(startDate.getMinutes());
        const startDay = pad(startDate.getDate());
        const startMonth = pad(startDate.getMonth() + 1);
        const startYear = startDate.getFullYear();

        // Nếu không có end_time, trả về định dạng chỉ ngày giờ bắt đầu
        if (!endIso) {
            return `${startHours}:${startMinutes} - ${startDay}/${startMonth}/${startYear}`;
        }

        const endDate = new Date(endIso);
        if (isNaN(endDate.getTime())) {
            return `${startHours}:${startMinutes} - ${startDay}/${startMonth}/${startYear}`;
        }

        const endHours = pad(endDate.getHours());
        const endMinutes = pad(endDate.getMinutes());
        const endDay = pad(endDate.getDate());
        const endMonth = pad(endDate.getMonth() + 1);
        const endYear = endDate.getFullYear();

        // Kiểm tra xem có cùng ngày hay không
        const isSameDay = startDay === endDay && startMonth === endMonth && startYear === endYear;

        if (isSameDay) {
            return `${startHours}:${startMinutes} - ${endHours}:${endMinutes}, ${startDay}/${startMonth}/${startYear}`;
        } else {
            return `${startHours}:${startMinutes}, ${startDay}/${startMonth}/${startYear} - ${endHours}:${endMinutes}, ${endDay}/${endMonth}/${endYear}`;
        }
    } catch (e) {
        return startIso;
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
 * Xử lý sự kiện khi bắt đầu kéo (drag start) một thẻ thành viên
 * @param {DragEvent} e
 */
window.handleMemberDragStart = function(e) {
    draggedMemberElement = e.currentTarget;
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.id);
};

/**
 * Xử lý sự kiện khi thả (drop) thành viên vào vùng mục tiêu (vùng đã chọn hoặc vùng khả dụng)
 * @param {DragEvent} e
 * @param {string} targetZoneType - Loại vùng đích ('selected' hoặc 'available')
 */
window.handleMemberDrop = function(e, targetZoneType) {
    e.preventDefault();
    if (!draggedMemberElement) return;

    // Xác định container đích dựa theo tham số truyền vào
    const targetZone = targetZoneType === 'selected'
        ? document.getElementById('selectedMembersZone')
        : document.getElementById('availableMembersZone');

    if (!targetZone) return;

    // Thêm nút xóa (chip) nếu chuyển vào vùng 'selected', ngược lại thì gỡ bỏ nút xóa
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

    // Di chuyển phần tử vào vùng đích và cập nhật lại input ẩn
    targetZone.appendChild(draggedMemberElement);
    window.updateMemberHiddenInput();
    draggedMemberElement = null;
};

/**
 * Xóa một thành viên khỏi danh sách đã chọn thông qua nút "x" trên chip và trả về danh sách khả dụng
 * @param {HTMLElement} btn - Nút 'x' được click
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
 * Cập nhật giá trị ID của các thành viên được chọn vào thẻ input ẩn để chuẩn bị gửi form
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

    // Tải toàn bộ danh sách sự kiện và khảo sát ngay khi trang được tải xong
    fetchAllEvents();

    // ---------------------------------------------------------------------
    // Quản lý Bật/Tắt Modal (Popup)
    // ---------------------------------------------------------------------
    /**
     * Hàm điều khiển hiển thị hoặc ẩn hộp thoại modal
     * @param {string} modalId - ID của modal cần thay đổi trạng thái
     * @param {boolean} show - True để hiển thị, false để ẩn
     */
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

    // Sự kiện mở modal tạo sự kiện mới khi click vào nút tương ứng
    document.getElementById('openCreateModalBtn')?.addEventListener('click', () => toggleModal('createEventModal', true));

    // Sự kiện đóng modal khi người dùng bấm vào các nút có class 'close-btn'
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

        // Lấy tất cả các thành viên đang có ở vùng khả dụng và chuyển sang vùng đã chọn
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
            // Nếu chọn khảo sát (POLL) thì hiển thị cấu hình poll, ngược lại hiển thị cấu hình lịch cố định
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

            // Nếu check "Cả ngày", chuyển kiểu input từ datetime-local sang date
            if (this.checked) {
                startTimeInput.type = 'date';
                endTimeInput.type = 'date';

                const today = new Date().toISOString().split('T')[0];
                let startVal = startTimeInput.value ? startTimeInput.value.split('T')[0] : today;
                let endVal = endTimeInput.value ? endTimeInput.value.split('T')[0] : startVal;

                startTimeInput.value = startVal;
                endTimeInput.value = endVal;
            } else {
                // Ngược lại, đưa về kiểu chọn ngày giờ chi tiết
                startTimeInput.type = 'datetime-local';
                endTimeInput.type = 'datetime-local';

                startTimeInput.value = '';
                endTimeInput.value = '';
            }
        });
    }

    // ---------------------------------------------------------------------
    // Bật/Tắt nhóm cài đặt thông báo nhắc nhở
    // ---------------------------------------------------------------------
    const enableNotificationCheckbox = document.getElementById('enableNotification');
    const notifSettingsGroup = document.getElementById('notificationSettingsGroup');

    if (enableNotificationCheckbox && notifSettingsGroup) {
        enableNotificationCheckbox.addEventListener('change', function() {
            // Ẩn/hiện các tùy chọn mốc thời gian nhắc nhở dựa vào trạng thái checkbox bật thông báo
            notifSettingsGroup.style.display = this.checked ? 'block' : 'none';
        });
    }

    // ---------------------------------------------------------------------
    // API 1: Tải danh sách Sự kiện đã chốt và Khảo sát từ Server
    // ---------------------------------------------------------------------
    async function fetchAllEvents() {
        try {
            // Gọi song song 2 API lấy lịch đã chốt (CONFIRMED) và lịch khảo sát (POLL)
            const [resConfirmed, resPoll] = await Promise.all([
                fetch('/api/calendar?status=CONFIRMED', { headers: headers }),
                fetch('/api/calendar?status=POLL', { headers: headers })
            ]);

            const resultConfirmed = await resConfirmed.json();
            const resultPoll = await resPoll.json();

            // Đổ dữ liệu ra các container tương ứng trên giao diện
            if (confirmedContainer) renderEventList(confirmedContainer, resultConfirmed.data || [], 'confirmed');
            if (pollContainer) renderEventList(pollContainer, resultPoll.data || [], 'poll');

            cachedConfirmedEvents = resultConfirmed.data || [];
            renderWeeklyCalendar();
            renderMiniCalendar();
            // Gắn lại các trình lắng nghe sự kiện cho các nút vừa render
            bindEventListeners();
            bindDeleteEventListeners();
        } catch (err) {
            console.error('Lỗi tải danh sách lịch:', err);
        }
    }

    /**
     * Render danh sách sự kiện/khảo sát vào container tương ứng trên giao diện
     * @param {HTMLElement} targetContainer - Vùng chứa danh sách trên HTML
     * @param {Array} data - Mảng dữ liệu sự kiện
     * @param {string} type - Loại danh sách ('confirmed' hoặc 'poll')
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

            // Xây dựng nút hành động dựa trên trạng thái khảo sát và quyền hạn người dùng (Quản lý hay Thành viên)
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

            // Nút xóa sự kiện hiển thị riêng cho tài khoản quản lý
            let deleteBtnHtml = '';
            if (ev.is_manager) {
                deleteBtnHtml = `<button class="delete-event-btn" data-id="${eventId}" title="Xóa sự kiện" style="position: absolute; top: 12px; right: 12px; background: transparent; border: none; font-size: 20px; font-weight: bold; color: #dc3545; cursor: pointer; line-height: 1; padding: 0 5px;">&times;</button>`;
            }

            let timeDisplayHtml = '';
            if (ev.start_time) {
                timeDisplayHtml = `<p><strong>Thời gian:</strong> ${formatEventDateTime(ev.start_time, ev.end_time)}</p>`;
            }

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

    /**
     * Gắn sự kiện click cho các nút Điền Lịch Rảnh và Xem Báo Cáo
     */
    function bindEventListeners() {
        // Sự kiện mở bảng điền lịch rảnh
        document.querySelectorAll('.fill-poll-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                activeEventId = this.getAttribute('data-id');

                let config = {};
                try {
                    config = JSON.parse(this.getAttribute('data-config'));
                } catch (err) {
                    console.error("Lỗi parse cấu hình poll:", err);
                }

                // Xây dựng bảng ma trận thời gian cho khảo sát
                buildPollMatrix(config, 'w2mMatrixTable', false);

                // Tải lịch rảnh cá nhân đã lưu trước đó (nếu có) để đánh dấu lại trên bảng
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

        // Sự kiện mở modal xem báo cáo thống kê khảo sát dành cho quản lý
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

                    // Dựng bảng heatmap thống kê mức độ rảnh rỗi của các thành viên
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
     * Gắn sự kiện xóa sự kiện cho tài khoản quản lý
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
                        fetchAllEvents(); // Tải lại danh sách sau khi xóa
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
    /**
     * Tạo bảng lưới thời gian cho khảo sát hoặc báo cáo thống kê
     * @param {Object} config - Cấu hình thời gian khảo sát
     * @param {string} tableId - ID của thẻ table trên HTML
     * @param {boolean} isReadonly - Chế độ chỉ đọc (dùng cho báo cáo) hay chế độ tương tác (dùng để điền lịch)
     * @param {Object} slotStats - Dữ liệu thống kê số lượng người rảnh theo từng ô (nếu có)
     */
    function buildPollMatrix(config, tableId, isReadonly = false, slotStats = null) {
        const table = document.getElementById(tableId);
        if (!table) return;
        table.innerHTML = '';

        const startDate = config.start_date ? new Date(config.start_date) : new Date();
        const endDate = config.end_date ? new Date(config.end_date) : new Date();
        const step = config.step_minutes || 30;

        // Tạo mảng danh sách các ngày từ ngày bắt đầu đến ngày kết thúc
        let dates = [];
        let curr = new Date(startDate);
        while (curr <= endDate) {
            dates.push(new Date(curr));
            curr.setDate(curr.getDate() + 1);
        }

        // Tạo mảng các mốc thời gian trong ngày dựa theo bước nhảy (step_minutes)
        let timeSlots = [];
        let totalMinutesStart = 6 * 60; // Bắt đầu từ 06:00 sáng
        let totalMinutesEnd = 24 * 60;   // Kết thúc lúc 24:00 đêm
        for (let m = totalMinutesStart; m < totalMinutesEnd; m += step) {
            let hh = String(Math.floor(m / 60)).padStart(2, '0');
            let mm = String(m % 60).padStart(2, '0');
            timeSlots.push(`${hh}:${mm}`);
        }
        timeSlots.push("24:00");

        let headerRow1 = '<tr><th class="time-col-header" rowspan="2">Thời gian</th>';
        let headerRow2 = '<tr>';

        // Xây dựng dòng tiêu đề các ngày trong tuần
        dates.forEach((d, index) => {
            let dayName = d.toLocaleDateString('vi-VN', { weekday: 'short' });
            let dayMonth = `${d.getDate()}/${d.getMonth() + 1}`;

            headerRow1 += `<th>${dayName}</th>`;

            if (!isReadonly) {
                // Thêm nút "Chọn cả ngày" cho từng cột ngày ở chế độ điền lịch
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

        // Vẽ các dòng thời gian và các ô ô chọn (slot) tương ứng với từng ngày
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
                    // Chế độ xem báo cáo: Hiển thị số lượng người rảnh và tô màu heatmap tương ứng
                    let count = slotStats ? (slotStats[slotISO] || 0) : 0;
                    let heatmapClass = count > 0 ? (count > 2 ? 'heatmap-high' : 'heatmap-mid') : '';
                    row += `<td class="time-slot-cell ${heatmapClass}" data-slot="${slotISO}">${count}</td>`;
                } else {
                    // Chế độ điền lịch: Tạo ô trống có hỗ trợ bôi đen chọn lịch
                    let borderClass = (time !== "24:00" && time.endsWith('30')) ? 'slot-half-hour' : 'slot-full-hour';
                    row += `<td class="time-slot-cell ${borderClass}" data-col-index="${index}" data-slot="${slotISO}"></td>`;
                }
            });

            row += '</tr>';
            table.innerHTML += row;
        }

        // Gắn sự kiện cho nút "Chọn cả ngày" trên từng cột
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

        // Kích hoạt tính năng kéo chuột bôi đen chọn nhiều ô
        initDragSelection(table);
    }

    // ---------------------------------------------------------------------
    // 3. Tính năng kéo chuột bôi đen/chọn các ô thời gian (Drag selection)
    // ---------------------------------------------------------------------
    /**
     * Xây dựng cơ chế kéo thả chuột mượt mà (kiểu When2Meet) để bôi đen chọn khung giờ rảnh
     * @param {HTMLElement} table - Thẻ bảng ma trận
     */
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
        // Thu thập toàn bộ các ô thời gian đang được bôi đen chọn
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
                fetchAllEvents(); // Tải lại danh sách sự kiện sau khi gửi
            } else {
                alert(result.message || 'Lỗi gửi lịch rảnh.');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối máy chủ.');
        }
    });

    // ---------------------------------------------------------------------
    // 5. Submit Tạo Sự Kiện / Khảo Sát Mới
    // ---------------------------------------------------------------------
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

            // Thu thập tất cả các mốc thời gian nhắc nhở thông báo được tích chọn vào một mảng (Array)
            const selectedRemindCheckboxes = document.querySelectorAll('input[name="remindMinutes"]:checked');
            const remindMinutesArray = Array.from(selectedRemindCheckboxes).map(cb => parseInt(cb.value, 10));

            // Xây dựng đối tượng payload chứa thông tin sự kiện mới
            let payload = {
                title: document.getElementById('eventTitle').value,
                type: document.getElementById('eventType').value,
                status: statusVal,
                target_member_ids: targetMembersVal.split(',').map(s => s.trim()).filter(Boolean),
                notification_settings: {
                    enabled: remindMinutesArray.length > 0, // Tự động bật thông báo nếu có chọn ít nhất 1 mốc
                    remind_before_minutes: remindMinutesArray // Mảng chứa các mốc thời gian trước sự kiện
                }
            };

            // Phân tách dữ liệu cấu hình theo loại (Khảo sát POLL hay Lịch cố định CONFIRMED)
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
                    fetchAllEvents(); // Tải lại danh sách sự kiện sau khi tạo thành công
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

        // Lấy các khung giờ được quản lý bôi đen trực tiếp trên bảng báo cáo thống kê
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

        // Sắp xếp các mốc thời gian để tìm ra thời điểm bắt đầu sớm nhất và kết thúc muộn nhất
        slots.sort((a, b) => a - b);

        let startTimeObj = slots[0];
        let endTimeObj = new Date(slots[slots.length - 1]);
        endTimeObj.setMinutes(endTimeObj.getMinutes() + 30); // Tự động cộng thêm 30 phút cho khoảng thời gian cuối

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
                fetchAllEvents(); // Tải lại danh sách sau khi chốt lịch
            } else {
                alert(result.message || 'Lỗi chốt lịch.');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối máy chủ.');
        }
    });

    // =====================================================================
        // 7. QUẢN LÝ LỊCH THÁNG NHỎ VÀ LỊCH TUẦN LỚN (UI CALENDAR VIEW - REWRITTEN)
        // =====================================================================
        let currentWeekStartDate = new Date();
        // Đưa về ngày thứ Hai đầu tuần
        let dayOfWeek = currentWeekStartDate.getDay();
        let diffToMonday = currentWeekStartDate.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
        currentWeekStartDate.setDate(diffToMonday);
        currentWeekStartDate.setHours(0, 0, 0, 0);

        let currentMiniMonthDate = new Date();
        let cachedConfirmedEvents = [];

        // --- RENDER LỊCH THÁNG NHỎ (SIDEBAR) ---
        function renderMiniCalendar() {
            const grid = document.getElementById('miniCalendarGrid');
            const label = document.getElementById('miniCalendarMonthYear');
            if (!grid || !label) return;

            grid.innerHTML = '';
            let year = currentMiniMonthDate.getFullYear();
            let month = currentMiniMonthDate.getMonth();

            label.innerText = `Tháng ${month + 1}, ${year}`;

            let firstDayIndex = new Date(year, month, 1).getDay();
            firstDayIndex = firstDayIndex === 0 ? 6 : firstDayIndex - 1; // Chuẩn Thứ 2 - Chủ Nhật
            let totalDays = new Date(year, month + 1, 0).getDate();

            // Ô trống đầu tháng
            for (let i = 0; i < firstDayIndex; i++) {
                grid.innerHTML += `<span></span>`;
            }

            let todayStr = new Date().toISOString().split('T')[0];
            for (let d = 1; d <= totalDays; d++) {
                let dateObj = new Date(year, month, d);
                let dateStr = dateObj.toISOString().split('T')[0];
                let isToday = dateStr === todayStr;

                let hasEvent = cachedConfirmedEvents.some(ev => ev.start_time && ev.start_time.startsWith(dateStr));

                let style = `padding: 4px 0; text-align: center; border-radius: 4px; cursor: pointer; position: relative; font-size: 12px;`;
                if (isToday) {
                    style += ` background: #3b82f6; color: #fff; font-weight: bold;`;
                } else {
                    style += ` color: #334155;`;
                }

                let dot = hasEvent ? `<span style="position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); width: 4px; height: 4px; background: ${isToday ? '#fff' : '#10b981'}; border-radius: 50%;"></span>` : '';

                let cell = document.createElement('div');
                cell.className = 'mini-day-cell';
                cell.style.cssText = style;
                cell.innerHTML = `${d}${dot}`;
                cell.dataset.date = dateStr;
                grid.appendChild(cell);
            }

            // Click ngày ở lịch nhỏ -> chuyển tuần tương ứng ở lịch lớn
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


        // --- RENDER LỊCH TUẦN LỚN (00:00 ĐẾN 23:00 + NHÃN 24:00 Ở CUỐI) ---
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

                // Chỉ tạo 24 mốc giờ từ 0 đến 23 (bỏ hàng 24)
                let hours = [];
                for (let h = 0; h < 24; h++) {
                    hours.push(h);
                }

                grid.style.display = 'grid';
                grid.style.gridTemplateColumns = '60px repeat(7, 1fr)';
                // 24 hàng giờ + 1 hàng phụ đáy để hiển thị vạch đóng và nhãn 24:00
                grid.style.gridTemplateRows = `45px repeat(24, 45px) 20px`;

                // 1. Header Cột Ngày (Hàng 1)
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

                // 2. Render Khung Lưới Từ 00:00 đến 23:00
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
                        cellDiv.style.cssText = `
                            border-right: 1px solid #e2e8f0;
                            border-top: 1px solid #f1f5f9;
                            grid-column: ${colIndex + 2};
                            grid-row: ${rowIndex};
                            position: relative;
                            box-sizing: border-box;
                            background: transparent;
                        `;
                        grid.appendChild(cellDiv);

                        if (!dayCellMatrix[colIndex]) dayCellMatrix[colIndex] = [];
                        dayCellMatrix[colIndex][hour] = cellDiv;
                    });
                });

                // 3. Thêm dòng đáy chứa nhãn "24:00" ngay dưới vạch 23:00
                let bottomRowIndex = hours.length + 2;
                let footerTimeDiv = document.createElement('div');
                footerTimeDiv.style.cssText = `padding: 2px 6px; font-size: 11px; color: #94a3b8; text-align: right; border-right: 1px solid #e2e8f0; border-top: 1px solid #f1f5f9; grid-row: ${bottomRowIndex}; grid-column: 1;`;
                footerTimeDiv.innerText = '24:00';
                grid.appendChild(footerTimeDiv);

                weekDays.forEach((d, colIndex) => {
                    let footerCellDiv = document.createElement('div');
                    footerCellDiv.style.cssText = `
                        border-right: 1px solid #e2e8f0;
                        border-top: 1px solid #f1f5f9;
                        grid-column: ${colIndex + 2};
                        grid-row: ${bottomRowIndex};
                        background: transparent;
                    `;
                    grid.appendChild(footerCellDiv);
                });

                // 4. Đổ dữ liệu sự kiện vào lịch
                cachedConfirmedEvents.forEach(ev => {
                    if (!ev.start_time) return;
                    let evStart = new Date(ev.start_time);
                    let evEnd = ev.end_time ? new Date(ev.end_time) : new Date(evStart.getTime() + 60 * 60 * 1000);

                    weekDays.forEach((d, colIndex) => {
                        let dateStr = d.toISOString().split('T')[0];
                        let dayStart = new Date(d);
                        dayStart.setHours(0, 0, 0, 0);
                        let dayEnd = new Date(d);
                        dayEnd.setHours(23, 59, 59, 999);

                        if (evEnd >= dayStart && evStart <= dayEnd) {
                            let actualStart = evStart < dayStart ? dayStart : evStart;
                            let actualEnd = evEnd > dayEnd ? dayEnd : evEnd;

                            let startHour = actualStart.getHours();
                            let startMinute = actualStart.getMinutes();

                            let endTotalMinutes = actualEnd.getHours() * 60 + actualEnd.getMinutes();
                            if (evEnd > dayEnd && dateStr === evStart.toISOString().split('T')[0]) {
                                endTotalMinutes = 24 * 60;
                            }
                            let startTotalMinutes = startHour * 60 + startMinute;
                            let durationMinutes = endTotalMinutes - startTotalMinutes;
                            if (durationMinutes < 15) durationMinutes = 15;

                            let targetCell = dayCellMatrix[colIndex] ? dayCellMatrix[colIndex][startHour] : null;
                            if (!targetCell) return;

                            let topPx = (startMinute / 60) * 45;
                            let heightPx = (durationMinutes / 60) * 45 - 2;

                            let eventCard = document.createElement('div');
                            eventCard.style.cssText = `
                                position: absolute;
                                top: ${topPx}px;
                                left: 2px;
                                right: 2px;
                                height: ${heightPx}px;
                                background: #e0f2fe;
                                border-left: 3px solid #0284c7;
                                padding: 4px 6px;
                                border-radius: 4px;
                                font-size: 11px;
                                overflow: hidden;
                                z-index: 5;
                                box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                                box-sizing: border-box;
                            `;
                            eventCard.innerHTML = `
                                <strong style="color: #0369a1; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${ev.title}</strong>
                                <span style="color: #64748b; font-size: 10px;">${formatEventDateTime(ev.start_time, ev.end_time)}</span>
                            `;
                            targetCell.appendChild(eventCard);
                        }
                    });
                });
            }

            // --- ĐIỀU HƯỚNG TUẦN (Đảm bảo đủ 3 nút: Prev, Next, Today) ---
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
