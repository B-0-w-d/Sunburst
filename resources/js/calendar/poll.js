// =========================================================================
// HÀM TÍNH TOÁN DEADLINE TỪ SỐ NGÀY THỜI HẠN (ĐẦU GIỜ NGÀY HÔM SAU)
// =========================================================================

/**
 * Tính toán mốc thời gian deadline là 00:00:00 của ngày hôm sau tính từ ngày kết thúc + số ngày thời hạn
 * @param {string} endDateStr - Ngày kết thúc khảo sát (YYYY-MM-DD)
 * @param {number|string} durationDays - Số ngày cho phép điền lịch hoặc 'unlimited'
 * @returns {string|null} - Chuỗi định dạng chuẩn gửi lên backend hoặc null nếu không thời hạn
 */
export function calculatePollDeadline(endDateStr, durationDays) {
    if (durationDays === 'unlimited' || durationDays === null) {
        return null;
    }

    const parseLocalDate = (dateStr) => {
        if (!dateStr) return new Date();
        const cleanStr = dateStr.split('T')[0];
        const [y, m, d] = cleanStr.split('-').map(Number);
        return new Date(y || new Date().getFullYear(), (m || 1) - 1, d || 1);
    };

    let baseDate = endDateStr ? parseLocalDate(endDateStr) : new Date();
    const days = parseInt(durationDays || 7, 10);
    baseDate.setDate(baseDate.getDate() + days);
    baseDate.setDate(baseDate.getDate() + 1);
    baseDate.setHours(0, 0, 0, 0);

    const pad = (n) => String(n).padStart(2, '0');
    const yyyy = baseDate.getFullYear();
    const mm = pad(baseDate.getMonth() + 1);
    const dd = pad(baseDate.getDate());
    const hh = pad(baseDate.getHours());
    const min = pad(baseDate.getMinutes());
    const ss = pad(baseDate.getSeconds());

    return `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss}`;
}

// =========================================================================
// DỰNG BẢNG MA TRẬN KHẢO SÁT, HEATMAP VÀ CHỐT LỊCH (CÓ TÍCH HỢP HOVER & CLICK)
// =========================================================================

/**
 * Hàm dựng bảng ma trận thời gian cho form điền lịch rảnh hoặc bảng báo cáo chốt lịch
 * @param {Object} config - Cấu hình khảo sát (ngày bắt đầu, ngày kết thúc, bước nhảy thời gian...)
 * @param {string} tableId - ID của thẻ table HTML cần render
 * @param {boolean} isReadonly - Chế độ (false: điền lịch rảnh, true: xem báo cáo heatmap/chốt lịch)
 * @param {Object} slotStats - Thống kê số lượng phản hồi cho từng khung giờ (dùng cho chế độ báo cáo)
 */
export function buildPollMatrix(config, tableId, isReadonly = false, slotStats = null) {
    const table = document.getElementById(tableId);
    if (!table) return;
    table.innerHTML = ''; // Làm sạch bảng trước khi render mới

    // Hàm phụ parse ngày tháng dạng YYYY-MM-DD theo giờ cục bộ (local time) tránh bị lệch múi giờ UTC
    const parseLocalDate = (dateStr) => {
        if (!dateStr) return new Date();
        const cleanStr = dateStr.split('T')[0];
        const [y, m, d] = cleanStr.split('-').map(Number);
        return new Date(y || new Date().getFullYear(), (m || 1) - 1, d || 1);
    };

    // Xác định ngày bắt đầu, kết thúc và bước nhảy (mặc định 30 phút)
    const startDate = config.start_date ? parseLocalDate(config.start_date) : new Date();
    const endDate = config.end_date ? parseLocalDate(config.end_date) : new Date();
    const step = config.step_minutes || 30;

    // Tạo mảng danh sách các ngày trong khoảng thời gian khảo sát
    let dates = [];
    let curr = new Date(startDate);
    while (curr <= endDate) {
        dates.push(new Date(curr));
        curr.setDate(curr.getDate() + 1);
    }

    // Tạo mảng các khung giờ trong ngày (từ 06:00 sáng đến 24:00 đêm)
    let timeSlots = [];
    let totalMinutesStart = 6 * 60;
    let totalMinutesEnd = 24 * 60;
    for (let m = totalMinutesStart; m < totalMinutesEnd; m += step) {
        let hh = String(Math.floor(m / 60)).padStart(2, '0');
        let mm = String(m % 60).padStart(2, '0');
        timeSlots.push(`${hh}:${mm}`);
    }
    timeSlots.push("24:00");

    // Xây dựng phần tiêu đề bảng (Header hàng 1: Thứ trong tuần, Header hàng 2: Ngày/Tháng)
    let headerRow1 = '<tr><th class="time-col-header"></th>';
    let headerRow2 = '<tr><th class="time-sub-header">Thời gian</th>';

    dates.forEach((d, index) => {
        let dayName = d.toLocaleDateString('vi-VN', { weekday: 'short' });
        let dayMonth = `${d.getDate()}/${d.getMonth() + 1}`;

        headerRow1 += `<th>${dayName}</th>`;

        // Nếu là chế độ điền lịch (không phải readonly), thêm nút "Chọn cả ngày" vào header
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
    table.innerHTML = headerRow1 + headerRow2;

    // Vòng lặp render các hàng thời gian và các ô (cell) giao nhau với từng ngày
    for (let i = 0; i < timeSlots.length; i++) {
        let time = timeSlots[i];
        let row = '<tr>';

        // Định dạng nhãn hiển thị cột thời gian bên trái
        if (time === "24:00") {
            row += `<td class="time-label-cell"><span>12:00 AM</span></td>`;
        } else {
            let [hh, mm] = time.split(':').map(Number);
            let hour12 = hh === 0 ? 12 : (hh > 12 ? hh - 12 : hh);
            let period = hh >= 12 ? 'PM' : 'AM';
            let timeFormatted = `${hour12}:${String(mm).padStart(2, '0')} ${period}`;
            row += `<td class="time-label-cell"><span>${timeFormatted}</span></td>`;
        }

        // Render các ô slot tương ứng với từng ngày trong tuần
        dates.forEach((d, index) => {
            const pad = (n) => String(n).padStart(2, '0');
            let dateStr = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            let slotISO = `${dateStr}T${time === "24:00" ? "00:00" : time}:00`;

            if (isReadonly) {
                // Chế độ xem báo cáo (Heatmap): Thêm hiệu ứng hover và con trỏ pointer
                let count = slotStats ? (slotStats[slotISO] || 0) : 0;
                let heatmapClass = count > 0 ? (count >= 3 ? 'heatmap-high' : 'heatmap-mid') : 'heatmap-low';
                row += `<td class="time-slot-cell ${heatmapClass} selectable-slot" data-slot="${slotISO}" title="Khung giờ này có ${count} người rảnh. Nhấn hoặc kéo để chọn chốt lịch." style="cursor: pointer;">
                            <span style="pointer-events: none; display: block; width: 100%; height: 100%;">${count}</span>
                        </td>`;
            } else {
                // Chế độ điền lịch rảnh cá nhân: Thêm hiệu ứng hover, con trỏ pointer và class phân chia giờ
                let borderClass = (time !== "24:00" && time.endsWith('30')) ? 'slot-half-hour' : 'slot-full-hour';
                row += `<td class="time-slot-cell ${borderClass}" data-col-index="${index}" data-slot="${slotISO}" title="Nhấn hoặc kéo để chọn khung giờ ${slotISO}" style="cursor: pointer;"></td>`;
            }
        });

        row += '</tr>';
        table.innerHTML += row;
    }

    if (!isReadonly) {
        // Gắn sự kiện click cho nút "Chọn cả ngày" ở mỗi cột ngày
        table.querySelectorAll('.select-col-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const colIndex = this.getAttribute('data-col-index');
                const colCells = table.querySelectorAll(`.time-slot-cell[data-col-index="${colIndex}"]`);

                const selectedCount = Array.from(colCells).filter(c => c.classList.contains('selected')).length;
                const shouldSelect = selectedCount < colCells.length;

                // Toggle trạng thái chọn cho toàn bộ ô trong cột
                colCells.forEach(cell => cell.classList.toggle('selected', shouldSelect));

                // Thay đổi giao diện nút sau khi bấm
                this.innerText = shouldSelect ? 'Bỏ chọn' : 'Chọn cả ngày';
                this.style.background = shouldSelect ? '#007bff' : '#f8f9fa';
                this.style.color = shouldSelect ? '#fff' : '#000';
            });
        });

        // Kích hoạt tính năng kéo chuột bôi đen & click chọn nhanh
        initTableDragSelection(table, false);
        initTableClickSelection(table);
    } else {
        // Kích hoạt tính năng kéo chuột bôi đen & click chọn nhanh cho báo cáo
        initTableDragSelection(table, true);
        initTableClickSelection(table);
    }
}

/**
 * Cơ chế kéo chuột bôi đen mượt mà trên bảng ma trận (Hỗ trợ cả điền lịch và chọn giờ chốt)
 * @param {HTMLElement} table - Thẻ table áp dụng sự kiện
 * @param {boolean} isReportMode - Xác định chế độ báo cáo hay điền lịch
 */
function initTableDragSelection(table, isReportMode = false) {
    let isMouseDown = false;
    let isSelecting = true;

    // Xóa sự kiện mouseup cũ toàn cục nếu đã tồn tại để tránh bị gán chồng lặp sự kiện
    if (window._globalMouseUpHandler) {
        document.removeEventListener('mouseup', window._globalMouseUpHandler);
    }

    // Lắng nghe sự kiện thả chuột trên toàn bộ document để kết thúc quá trình kéo
    window._globalMouseUpHandler = () => {
        isMouseDown = false;
    };
    document.addEventListener('mouseup', window._globalMouseUpHandler);

    // Bắt đầu nhấn chuột vào một ô thời gian
    table.addEventListener('mousedown', (e) => {
        const cell = e.target.closest('.time-slot-cell');
        if (!cell) return;

        isMouseDown = true;
        const checkClass = isReportMode ? 'selected-final-slot' : 'selected';
        isSelecting = !cell.classList.contains(checkClass); // Quyết định chọn thêm hay bỏ chọn

        if (isReportMode) {
            cell.classList.toggle('selected', isSelecting);
            cell.classList.toggle('selected-final-slot', isSelecting);
        } else {
            cell.classList.toggle('selected', isSelecting);
        }

        e.preventDefault(); // Ngăn chặn hành vi bôi đen văn bản mặc định của trình duyệt
    });

    // Kéo chuột qua các ô khác để bôi đen liên tục
    table.addEventListener('mouseover', (e) => {
        if (!isMouseDown) return;
        const cell = e.target.closest('.time-slot-cell');
        if (!cell) return;

        if (isReportMode) {
            cell.classList.toggle('selected', isSelecting);
            cell.classList.toggle('selected-final-slot', isSelecting);
        } else {
            cell.classList.toggle('selected', isSelecting);
        }
    });
}

/**
 * Lắng nghe sự kiện click đơn trên các ô thời gian để bật/tắt chọn nhanh
 * @param {HTMLElement} table - Thẻ table áp dụng sự kiện
 */
export function initTableClickSelection(table) {
    table.addEventListener('click', (e) => {
        const cell = e.target.closest('.time-slot-cell');
        if (!cell) return;

        // Kiểm tra xem đang ở chế độ nào để toggle class phù hợp
        const isReportMode = cell.classList.contains('selectable-slot') || cell.classList.contains('selected-final-slot');

        if (isReportMode) {
            cell.classList.toggle('selected-final-slot');
            cell.classList.toggle('selected');
        } else {
            cell.classList.toggle('selected');
        }
    });
}

/**
 * Hàm dựng bảng báo cáo Heatmap dựa trên dữ liệu thống kê khảo sát
 * @param {Object} config - Cấu hình thời gian khảo sát
 * @param {Object} slotStats - Dữ liệu thống kê số lượng người rảnh theo từng khung giờ
 * @param {number} targetCount - Tổng số lượng mục tiêu cần tham gia
 */
export function buildHeatmapMatrix(config, slotStats, targetCount) {
    // Gọi lại hàm buildPollMatrix với chế độ readonly = true để hiển thị số liệu thống kê
    buildPollMatrix(config, 'reportMatrixTable', true, slotStats);
}
