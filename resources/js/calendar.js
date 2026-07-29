// Lấy token từ localStorage
const token = localStorage.getItem('access_token');

const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}` // Gửi kèm Bearer Token để Sanctum xác thực
};

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('eventList');
    if (!container) return;

    let currentTab = 'confirmed';
    let activeEventId = null;
    let isMouseDown = false;
    let isSelecting = true;

    // Load danh sách ban đầu
    fetchEvents(currentTab);

    // Chuyển Tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentTab = this.getAttribute('data-tab');
            fetchEvents(currentTab);
        });
    });

    // Quản lý Modal chung
    function toggleModal(modalId, show = true) {
        document.getElementById(modalId)?.classList.toggle('active', show);
    }

    document.getElementById('openCreateModalBtn')?.addEventListener('click', () => toggleModal('createEventModal', true));
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            toggleModal(this.getAttribute('data-modal'), false);
        });
    });

    // Toggle form tạo sự kiện giữa POLL và CONFIRMED
    document.getElementById('eventStatus')?.addEventListener('change', function () {
        const isPoll = this.value === 'POLL';
        const pollSec = document.getElementById('pollConfigSection');
        const confSec = document.getElementById('confirmedConfigSection');
        if (pollSec) pollSec.style.display = isPoll ? 'block' : 'none';
        if (confSec) confSec.style.display = isPoll ? 'none' : 'block';
    });

    // 1. Fetch danh sách sự kiện từ API GET /api/calendar
    async function fetchEvents(status) {
        try {
            const res = await fetch(`/api/calendar?status=${status.toUpperCase()}`, {
                headers: headers
            });
            const result = await res.json();
            container.innerHTML = '';

            if (!result.data || result.data.length === 0) {
                container.innerHTML = '<p class="subtitle">Không có sự kiện nào.</p>';
                return;
            }

            result.data.forEach(ev => {
                const card = document.createElement('div');
                card.className = 'event-card';

                let actionBtn = '';
                if (ev.status === 'POLL') {
                    actionBtn = `<button class="btn btn-primary w-100 fill-poll-btn" data-id="${ev._id}" data-config='${JSON.stringify(ev.poll_config)}'>Điền Lịch Rảnh</button>`;
                    actionBtn += `<button class="btn btn-outline w-100 mt-2 view-report-btn" data-id="${ev._id}" data-title="${ev.title}">Xem Báo Cáo & Chốt Lịch</button>`;
                }

                card.innerHTML = `
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
                container.appendChild(card);
            });

            bindEventListeners();
        } catch (err) {
            console.error('Lỗi tải danh sách lịch:', err);
        }
    }

    function bindEventListeners() {
        document.querySelectorAll('.fill-poll-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                activeEventId = this.getAttribute('data-id');
                const config = JSON.parse(this.getAttribute('data-config'));
                buildWhen2MeetMatrix(config, 'w2mMatrixTable', false);
                toggleModal('when2meetModal', true);
            });
        });

        document.querySelectorAll('.view-report-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                activeEventId = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const reportTitle = document.getElementById('reportTitle');
                if (reportTitle) reportTitle.innerText = `Báo Cáo: ${title}`;

                try {
                    const res = await fetch(`/api/calendar/${activeEventId}/poll-report`, {
                        headers: headers
                    });
                    const json = await res.json();
                    document.getElementById('statTargetCount').innerText = `Tổng mục tiêu: ${json.target_count}`;
                    document.getElementById('statSubmittedCount').innerText = `Đã phản hồi: ${json.submitted_count}`;

                    buildHeatmapMatrix(json.slot_statistics, json.target_count);
                    toggleModal('reportModal', true);
                } catch (e) {
                    alert('Không thể tải báo cáo.');
                }
            });
        });
    }

    function buildWhen2MeetMatrix(config, tableId, isReadonly = false, slotStats = null) {
        const table = document.getElementById(tableId);
        if (!table) return;
        table.innerHTML = '';

        const startDate = new Date(config.start_date);
        const endDate = new Date(config.end_date);
        const [dStartH, dStartM] = config.daily_start_time.split(':').map(Number);
        const [dEndH, dEndM] = config.daily_end_time.split(':').map(Number);
        const step = config.step_minutes || 30;

        let dates = [];
        let curr = new Date(startDate);
        while (curr <= endDate) {
            dates.push(new Date(curr));
            curr.setDate(curr.getDate() + 1);
        }

        let timeSlots = [];
        let totalMinutesStart = dStartH * 60 + dStartM;
        let totalMinutesEnd = dEndH * 60 + dEndM;
        for (let m = totalMinutesStart; m < totalMinutesEnd; m += step) {
            let hh = String(Math.floor(m / 60)).padStart(2, '0');
            let mm = String(m % 60).padStart(2, '0');
            timeSlots.push(`${hh}:${mm}`);
        }

        let headerRow = '<tr><th>Thời gian</th>';
        dates.forEach(d => {
            headerRow += `<th>${d.toISOString().split('T')[0]}</th>`;
        });
        headerRow += '</tr>';
        table.innerHTML += headerRow;

        timeSlots.forEach(time => {
            let row = `<tr><td><strong>${time}</strong></td>`;
            dates.forEach(d => {
                let dateStr = d.toISOString().split('T')[0];
                let slotISO = `${dateStr}T${time}:00`;

                if (isReadonly) {
                    let count = slotStats ? (slotStats[slotISO] || 0) : 0;
                    let heatmapClass = count > 0 ? (count > 2 ? 'heatmap-high' : 'heatmap-mid') : '';
                    row += `<td class="time-slot-cell ${heatmapClass}">${count}</td>`;
                } else {
                    row += `<td class="time-slot-cell" data-slot="${slotISO}"></td>`;
                }
            });
            row += '</tr>';
            table.innerHTML += row;
        });

        if (!isReadonly) {
            initDragSelection(table);
        }
    }

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
            daily_start_time: "18:00",
            daily_end_time: "22:00",
            step_minutes: 30
        };
        buildWhen2MeetMatrix(sampleConfig, 'reportMatrixTable', true, slotStats);
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
                toggleModal('when2meetModal', false);
            } else {
                alert(result.message || 'Lỗi gửi lịch rảnh.');
            }
        } catch (e) {
            console.error(e);
        }
    });

    document.getElementById('createEventForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const statusVal = document.getElementById('eventStatus').value;

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
            if (res.ok) {
                alert('Tạo thành công!');
                toggleModal('createEventModal', false);
                fetchEvents(currentTab);
            } else {
                const err = await res.json();
                alert(err.message || 'Lỗi tạo sự kiện.');
            }
        } catch (ex) {
            console.error(ex);
        }
    });

    document.getElementById('confirmPollBtn')?.addEventListener('click', async function () {
        const startTime = document.getElementById('confirmStartTime').value;
        const endTime = document.getElementById('confirmEndTime').value;

        if (!startTime || !endTime) {
            alert('Vui lòng chọn thời gian bắt đầu và kết thúc để chốt lịch.');
            return;
        }

        try {
            const res = await fetch(`/api/calendar/${activeEventId}/confirm-poll`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ start_time: startTime, end_time: endTime })
            });
            if (res.ok) {
                alert('Đã chốt lịch thành công!');
                toggleModal('reportModal', false);
                fetchEvents(currentTab);
            } else {
                alert('Lỗi chốt lịch.');
            }
        } catch (e) {
            console.error(e);
        }
    });
});
