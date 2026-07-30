// =========================================================================
// CẤU HÌNH BAN ĐẦU & XÁC THỰC API
// =========================================================================
// Lấy token xác thực từ localStorage và thiết lập headers mặc định cho các request fetch API
const token = localStorage.getItem('access_token');

const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
};

// Đảm bảo DOM đã tải xong hoàn toàn trước khi thực thi mã JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Khai báo chính xác 2 container chứa danh sách Lịch đã chốt và Khảo sát đang mở
    const confirmedContainer = document.getElementById('confirmedEventList');
    const pollContainer = document.getElementById('pollEventList');

    // Nếu không tìm thấy các container này trên trang thì dừng lại để tránh lỗi script
    if (!confirmedContainer || !pollContainer) return;

    // Các biến trạng thái toàn cục trong scope
    let activeEventId = null;     // ID sự kiện đang được chọn thao tác (điền lịch hoặc xem báo cáo)
    let isMouseDown = false;      // Trạng thái giữ chuột để kéo bôi đen ma trận thời gian
    let isSelecting = true;       // Trạng thái đang chọn hay bỏ chọn khi kéo chuột

    // Gọi hàm tải toàn bộ dữ liệu sự kiện ngay khi vừa load trang
    fetchAllEvents();

    // =====================================================================
    // QUẢN LÝ MODAL (Đóng/Mở các cửa sổ Pop-up)
    // =====================================================================
    function toggleModal(modalId, show = true) {
        document.getElementById(modalId)?.classList.toggle('active', show);
    }

    // Sự kiện mở modal tạo sự kiện mới khi bấm nút
    document.getElementById('openCreateModalBtn')?.addEventListener('click', () => toggleModal('createEventModal', true));

    // Sự kiện đóng modal khi bấm nút chữ X hoặc nút đóng
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            toggleModal(this.getAttribute('data-modal'), false);
        });
    });

    // Thay đổi giao diện form tạo sự kiện tùy thuộc vào việc chọn Loại sự kiện hay Khảo sát (POLL)
    document.getElementById('eventStatus')?.addEventListener('change', function () {
        const isPoll = this.value === 'POLL';
        const pollSec = document.getElementById('pollConfigSection');
        const confSec = document.getElementById('confirmedConfigSection');
        if (pollSec) pollSec.style.display = isPoll ? 'block' : 'none';
        if (confSec) confSec.style.display = isPoll ? 'none' : 'block';
    });

    // =====================================================================
    // 1. TẢI ĐỒNG THỜI DANH SÁCH LỊCH ĐÃ CHỐT & KHẢO SÁT
    // =====================================================================
    async function fetchAllEvents() {
        try {
            // Gọi song song 2 API lấy dữ liệu trạng thái CONFIRMED và POLL cùng một lúc
            const [resConfirmed, resPoll] = await Promise.all([
                fetch('/api/calendar?status=CONFIRMED', { headers: headers }),
                fetch('/api/calendar?status=POLL', { headers: headers })
            ]);

            const resultConfirmed = await resConfirmed.json();
            const resultPoll = await resPoll.json();

            // Đổ dữ liệu vào các container tương ứng
            renderEventList(confirmedContainer, resultConfirmed.data, 'confirmed');
            renderEventList(pollContainer, resultPoll.data, 'poll');

            // Gắn lại sự kiện tương tác cho các nút chức năng trên card
            bindEventListeners();
            bindDeleteEventListeners();
        } catch (err) {
            console.error('Lỗi tải danh sách lịch:', err);
        }
    }

    // Hàm render danh sách các thẻ sự kiện card chung
    function renderEventList(targetContainer, data, type) {
        targetContainer.innerHTML = '';

        if (!data || data.length === 0) {
            targetContainer.innerHTML = `<p class="subtitle" style="color: #6c757d; font-style: italic;">Không có ${type === 'confirmed' ? 'lịch đã chốt' : 'khảo sát'} nào.</p>`;
            return;
        }

        // Duyệt qua từng bản ghi sự kiện để tạo thẻ card HTML
        data.forEach(ev => {
            const card = document.createElement('div');
            card.className = 'event-card';
            card.style.position = 'relative'; // Định vị tuyệt đối cho nút xóa ở góc

            let eventId = ev._id || ev.id || (ev._id && ev._id.$oid);
            let actionBtn = '';

            // Nếu là dạng khảo sát (POLL), thêm các nút chức năng "Điền Lịch Rảnh" và "Xem Báo Cáo"
            if (ev.status === 'POLL') {
                actionBtn = `<button class="btn btn-primary w-100 fill-poll-btn" data-id="${eventId}" data-config='${JSON.stringify(ev.poll_config)}'>Điền Lịch Rảnh</button>`;
                actionBtn += `<button class="btn btn-outline w-100 mt-2 view-report-btn" data-id="${eventId}" data-title="${ev.title}">Xem Báo Cáo & Chốt Lịch</button>`;
            }

            // Giao diện nút xóa sự kiện (dấu X đỏ góc phải card)
            const deleteBtnHtml = `<button class="delete-event-btn" data-id="${eventId}" title="Xóa sự kiện" style="position: absolute; top: 12px; right: 12px; background: transparent; border: none; font-size: 20px; font-weight: bold; color: #dc3545; cursor: pointer; line-height: 1; padding: 0 5px;">&times;</button>`;

            card.innerHTML = `
                ${deleteBtnHtml}
                <div>
                    <span class="badge badge-${ev.type.toLowerCase()}">${ev.type}</span>
                    <h4>${ev.title}</h4>
                    <div class="event-info">
                        <p><strong>Trạng thái:</strong> ${ev.status}</p>
                        ${ev.start_time ? `<p><strong>Bắt đầu:</strong> ${ev.start_time}</p>` : ''}
                    </div>
                </div>
                <div style="margin-top: 15px;">${actionBtn}</div>
            `;
            targetContainer.appendChild(card);
        });
    }

    // Gắn sự kiện click mở modal điền lịch rảnh hoặc xem báo cáo
    function bindEventListeners() {
        document.querySelectorAll('.fill-poll-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                activeEventId = this.getAttribute('data-id');
                const config = JSON.parse(this.getAttribute('data-config'));
                buildPollMatrix(config, 'w2mMatrixTable', false); // Xây dựng bảng điền lịch tương tác
                toggleModal('pollMatrixModal', true);
            });
        });

        document.querySelectorAll('.view-report-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                activeEventId = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const reportTitle = document.getElementById('reportTitle');
                if (reportTitle) reportTitle.innerText = `Báo Cáo: ${title}`;

                try {
                    // Gọi API lấy dữ liệu thống kê báo cáo khảo sát
                    const res = await fetch(`/api/calendar/${activeEventId}/poll-report`, {
                        headers: headers
                    });
                    const json = await res.json();
                    document.getElementById('statTargetCount').innerText = `Tổng mục tiêu: ${json.target_count}`;
                    document.getElementById('statSubmittedCount').innerText = `Đã phản hồi: ${json.submitted_count}`;

                    buildHeatmapMatrix(json.slot_statistics, json.target_count); // Xây dựng biểu đồ nhiệt báo cáo
                    toggleModal('reportModal', true);
                } catch (e) {
                    alert('Không thể tải báo cáo.');
                }
            });
        });
    }

    // =====================================================================
    // HÀM XỬ LÝ SỰ KIỆN XÓA SỰ KIỆN
    // =====================================================================
    function bindDeleteEventListeners() {
        document.querySelectorAll('.delete-event-btn').forEach(btn => {
            if (btn.dataset.deleteListenerAttached) return;
            btn.dataset.deleteListenerAttached = "true";

            btn.addEventListener('click', async function () {
                const eventId = this.getAttribute('data-id');

                if (!confirm('Bạn có chắc chắn muốn xóa sự kiện/poll này không?')) return;

                try {
                    const res = await fetch(`/api/calendar/${eventId}`, {
                        method: 'DELETE',
                        headers: headers
                    });

                    const result = await res.json();

                    if (res.ok) {
                        alert('Đã xóa thành công!');
                        fetchAllEvents(); // Tải lại toàn bộ dữ liệu 2 bảng sau khi xóa
                    } else {
                        alert(result.message || 'Không thể xóa sự kiện.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Lỗi kết nối đến server.');
                }
            });
        });
    }

    // =====================================================================
    // 2. KHỞI TẠO BẢNG MA TRẬN KHẢO SÁT LỊCH (W2M MATRIX)
    // =====================================================================
    function buildPollMatrix(config, tableId, isReadonly = false, slotStats = null) {
        const table = document.getElementById(tableId);
        if (!table) return;
        table.innerHTML = '';

        const startDate = new Date(config.start_date);
        const endDate = new Date(config.end_date);
        const step = config.step_minutes || 30;

        // Tạo mảng danh sách các ngày trong khoảng thời gian khảo sát
        let dates = [];
        let curr = new Date(startDate);
        while (curr <= endDate) {
            dates.push(new Date(curr));
            curr.setDate(curr.getDate() + 1);
        }

        // Tạo các khung giờ trong ngày dựa trên bước nhảy (mặc định từ 6h sáng đến 24h)
        let timeSlots = [];
        let totalMinutesStart = 6 * 60;
        let totalMinutesEnd = 24 * 60;
        for (let m = totalMinutesStart; m < totalMinutesEnd; m += step) {
            let hh = String(Math.floor(m / 60)).padStart(2, '0');
            let mm = String(m % 60).padStart(2, '0');
            timeSlots.push(`${hh}:${mm}`);
        }
        timeSlots.push("24:00");

        // Tạo tiêu đề bảng ma trận 2 dòng (Thứ và Ngày/Tháng)
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
                        <button type="button" class="select-col-btn" data-col-index="${index}" title="Chọn toàn bộ ngày này" style="font-size: 11px; padding: 2px 6px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: #f8f9fa;">Chọn cả ngày</button>
                    </div>
                </th>`;
            } else {
                headerRow2 += `<th>${dayMonth}</th>`;
            }
        });
        headerRow1 += '</tr>';
        headerRow2 += '</tr>';
        table.innerHTML += headerRow1 + headerRow2;

        // Duyệt qua từng khung giờ để vẽ các hàng trong bảng
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

            // Vẽ các ô tương tác chọn lịch hoặc hiển thị thống kê heatmap
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

        // Sự kiện nút "Chọn cả ngày" cho từng cột ngày
        if (!isReadonly) {
            table.querySelectorAll('.select-col-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const colIndex = this.getAttribute('data-col-index');
                    const colCells = table.querySelectorAll(`.time-slot-cell[data-col-index="${colIndex}"]`);

                    const selectedCount = Array.from(colCells).filter(c => c.classList.contains('selected')).length;
                    const shouldSelect = selectedCount < colCells.length;

                    colCells.forEach(cell => {
                        cell.classList.toggle('selected', shouldSelect);
                    });

                    this.innerText = shouldSelect ? 'Bỏ chọn' : 'Chọn cả ngày';
                    this.style.background = shouldSelect ? '#007bff' : '#f8f9fa';
                    this.style.color = shouldSelect ? '#fff' : '#000';
                });
            });
        }

        // Khởi tạo tính năng kéo chuột bôi đen ô lịch
        initDragSelection(table);
    }

    // =====================================================================
    // 3. TÍNH NĂNG KÉO CHUỘT BÔI ĐEN (DRAG SELECTION)
    // =====================================================================
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

    function buildHeatmapMatrix(slotStats, targetCount) {
        let sampleConfig = {
            start_date: "2026-08-01",
            end_date: "2026-08-05",
            step_minutes: 30
        };
        buildPollMatrix(sampleConfig, 'reportMatrixTable', true, slotStats);
    }

    // =====================================================================
    // 4. GỬI LỊCH RẢNH LÊN SERVER
    // =====================================================================
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
            } else {
                alert(result.message || 'Lỗi gửi lịch rảnh.');
            }
        } catch (e) {
            console.error(e);
        }
    });

    // =====================================================================
    // 5. TẠO SỰ KIỆN / KHẢO SÁT MỚI
    // =====================================================================
    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm && !createEventForm.dataset.listenerAttached) {
        createEventForm.dataset.listenerAttached = "true";

        let isCreating = false;
        createEventForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (isCreating) return;
            isCreating = true;

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const statusVal = document.getElementById('eventStatus').value;

            // Thu thập dữ liệu từ form tạo sự kiện
            let payload = {
                title: document.getElementById('eventTitle').value,
                type: document.getElementById('eventType').value,
                status: statusVal,
                target_member_ids: document.getElementById('targetMemberIds').value.split(',').map(s => s.trim()).filter(Boolean)
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
                    fetchAllEvents(); // Tải lại đồng thời cả 2 bảng
                } else {
                    alert(result.message || 'Lỗi tạo sự kiện.');
                }
            } catch (ex) {
                console.error(ex);
                alert('Lỗi kết nối đến server.');
            } finally {
                isCreating = false;
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // =====================================================================
    // 6. CHỐT LỊCH TỪ BÁO CÁO KHẢO SÁT
    // =====================================================================
    document.getElementById('confirmPollBtn')?.addEventListener('click', async function () {
        if (!activeEventId) {
            alert('Không tìm thấy ID sự kiện.');
            return;
        }

        const selectedCells = document.querySelectorAll('#reportMatrixTable .time-slot-cell.selected');
        if (selectedCells.length === 0) {
            alert('Vui lòng chọn khung giờ trên bảng báo cáo.');
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
                fetchAllEvents(); // Làm mới lại cả 2 danh sách
            } else {
                alert(result.message || 'Lỗi chốt lịch.');
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối đến server.');
        }
    });
});

// =========================================================================
// QUẢN LÝ KÉO THẢ THÀNH VIÊN (DRAG & DROP MEMBERS)
// =========================================================================
let draggedMemberElement = null;

window.handleMemberDragStart = function(e) {
    draggedMemberElement = e.currentTarget;
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.id);
}

window.handleMemberDrop = function(e, targetZoneType) {
    e.preventDefault();
    if (!draggedMemberElement) return;

    const targetZone = targetZoneType === 'selected'
        ? document.getElementById('selectedMembersZone')
        : document.getElementById('availableMembersZone');

    if (targetZoneType === 'selected' && !draggedMemberElement.querySelector('.btn-remove-chip')) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-remove-chip';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function() { window.removeMemberChip(this); };
        draggedMemberElement.appendChild(removeBtn);
    }
    else if (targetZoneType === 'available') {
        const btn = draggedMemberElement.querySelector('.btn-remove-chip');
        if (btn) btn.remove();
    }

    targetZone.appendChild(draggedMemberElement);
    window.updateMemberHiddenInput();
    draggedMemberElement = null;
}

window.removeMemberChip = function(btn) {
    const chip = btn.closest('.member-chip');
    const availableZone = document.getElementById('availableMembersZone');
    btn.remove();
    availableZone.appendChild(chip);
    window.updateMemberHiddenInput();
}

window.updateMemberHiddenInput = function() {
    const selectedZone = document.getElementById('selectedMembersZone');
    const chips = selectedZone.querySelectorAll('.member-chip');
    const ids = Array.from(chips).map(chip => chip.dataset.id);

    const hiddenInput = document.getElementById('targetMemberIds');
    if (hiddenInput) {
        hiddenInput.value = ids.join(',');
    }
}
