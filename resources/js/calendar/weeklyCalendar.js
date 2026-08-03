// =========================================================================
// QUẢN LÝ LỊCH TUẦN CHI TIẾT
// =========================================================================

import { formatEventDateTime } from './utils.js';
import { typeColors } from './calendar.js';

/**
 * Biến lưu trữ ngày bắt đầu của tuần hiện tại (mặc định tính từ Thứ Hai).
 * Được tính toán động dựa trên ngày hiện tại của hệ thống.
 */
export let currentWeekStartDate = (() => {
    let d = new Date();
    let day = d.getDay();
    // Quy đổi Chủ Nhật (day = 0) thành 7 để tính toán khoảng cách về Thứ 2 đầu tuần
    let diff = d.getDate() - day + (day === 0 ? -6 : 1);
    d.setDate(diff);
    d.setHours(0, 0, 0, 0);
    return d;
})();

/**
 * Hàm vẽ lưới lịch tuần, hiển thị các mốc giờ từ 00:00 đến 24:00
 * và định vị các sự kiện đã chốt lên đúng ô thời gian tương ứng.
 * @param {Array} cachedConfirmedEvents - Mảng chứa danh sách các sự kiện đã được chốt lịch
 * @param {Function} [onEventClick] - (Tùy chọn) Hàm callback khi người dùng click vào sự kiện để chỉnh sửa
 */
export function renderWeeklyCalendar(cachedConfirmedEvents, onEventClick = null) {
    const grid = document.getElementById('weeklyCalendarGrid');
    const titleLabel = document.getElementById('currentWeekTitle');
    if (!grid) return;

    // Làm sạch lưới lịch cũ trước khi render lại
    grid.innerHTML = '';

    // Tạo danh sách 7 ngày trong tuần (từ Thứ Hai đến Chủ Nhật) dựa trên currentWeekStartDate
    let weekDays = [];
    let tempDate = new Date(currentWeekStartDate);
    for (let i = 0; i < 7; i++) {
        weekDays.push(new Date(tempDate));
        tempDate.setDate(tempDate.getDate() + 1);
    }

    // Hiển thị tiêu đề khoảng thời gian của tuần lên giao diện (VD: Lịch Đã Chốt (3/8 - 9/8/2026))
    let startStr = `${weekDays[0].getDate()}/${weekDays[0].getMonth() + 1}`;
    let endStr = `${weekDays[6].getDate()}/${weekDays[6].getMonth() + 1}/${weekDays[6].getFullYear()}`;
    if (titleLabel) {
        titleLabel.innerHTML = `<span style="width: 10px; height: 10px; border-radius: 50%; background-color: #10b981; display: inline-block; margin-right: 6px;"></span> Lịch Đã Chốt (${startStr} - ${endStr})`;
    }

    // Khởi tạo danh sách 24 khung giờ trong ngày (từ 0h đến 23h)
    let hours = [];
    for (let h = 0; h < 24; h++) hours.push(h);

    const rowHeight = 34; // Chiều cao (px) tính cho mỗi dòng giờ trên lưới

    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = '60px repeat(7, 1fr)'; // Cột 1 chứa nhãn giờ, 7 cột tiếp theo cho 7 ngày trong tuần
    grid.style.gridTemplateRows = `45px repeat(24, ${rowHeight}px) 20px`; // Hàng 1 cho tiêu đề ngày, 24 hàng cho các giờ, hàng cuối cho mốc 24:00

    // Vẽ ô trống góc trên cùng bên trái (giao giữa cột giờ và hàng tiêu đề ngày)
    let headerTime = document.createElement('div');
    headerTime.style.cssText = `border-right: 1px solid #e2e8f0; background: #f8fafc; grid-row: 1; grid-column: 1;`;
    grid.appendChild(headerTime);

    // Render thanh tiêu đề 7 ngày trong tuần (Thứ 2 đến Chủ Nhật)
    weekDays.forEach((d, idx) => {
        let dayNames = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
        let isToday = new Date().toDateString() === d.toDateString(); // Kiểm tra có phải ngày hôm nay không để đổi màu nổi bật
        let headerDay = document.createElement('div');
        headerDay.style.cssText = `padding: 6px; text-align: center; border-right: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: ${isToday ? '#eff6ff' : '#f8fafc'}; grid-row: 1; grid-column: ${idx + 2};`;
        headerDay.innerHTML = `
            <div style="font-size: 11px; color: #64748b; font-weight: 600;">${dayNames[idx]}</div>
            <div style="font-size: 14px; font-weight: 700; color: ${isToday ? '#2563eb' : '#1e293b'};">${d.getDate()}/${d.getMonth()+1}</div>
        `;
        grid.appendChild(headerDay);
    });

    // Tạo ma trận lưu trữ các ô DOM theo từng giờ và từng ngày để dễ dàng nhét thẻ sự kiện vào sau đó
    let dayCellMatrix = [];
    hours.forEach((hour, hIndex) => {
        let rowIndex = hIndex + 2; // Dịch xuống 2 hàng vì hàng 1 dành cho tiêu đề ngày
        let timeLabelText = `${String(hour).padStart(2, '0')}:00`;

        // Render nhãn hiển thị giờ bên cột trái (VD: 08:00)
        let timeLabelDiv = document.createElement('div');
        timeLabelDiv.style.cssText = `padding: 4px 6px; font-size: 11px; color: #94a3b8; text-align: right; border-right: 1px solid #e2e8f0; border-top: 1px solid #f1f5f9; grid-row: ${rowIndex}; grid-column: 1;`;
        timeLabelDiv.innerText = timeLabelText;
        grid.appendChild(timeLabelDiv);

        // Render các ô lưới (cell) tương ứng cho từng ngày trong khung giờ đó
        weekDays.forEach((d, colIndex) => {
            let cellDiv = document.createElement('div');
            cellDiv.style.cssText = `border-right: 1px solid #e2e8f0; border-top: 1px solid #f1f5f9; grid-column: ${colIndex + 2}; grid-row: ${rowIndex}; position: relative; box-sizing: border-box; background: transparent;`;
            grid.appendChild(cellDiv);

            // Lưu trữ tham chiếu cell vào ma trận
            if (!dayCellMatrix[colIndex]) dayCellMatrix[colIndex] = [];
            dayCellMatrix[colIndex][hour] = cellDiv;
        });
    });

    // Render hàng chân trang cuối cùng (mốc 24:00 kết thúc ngày)
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

    /**
     * Hàm nội bộ chuyển đổi chuỗi thời gian thô thành đối tượng Date cục bộ,
     * tránh việc bị lệch múi giờ UTC so với thời gian hệ thống.
     */
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

    // Duyệt qua từng sự kiện đã chốt để tính toán tọa độ và gắn lên lịch tuần
    cachedConfirmedEvents.forEach(ev => {
        if (!ev.start_time) return;
        let evStart = parseLocalDateTime(ev.start_time);
        let evEnd = ev.end_time ? parseLocalDateTime(ev.end_time) : new Date(evStart.getTime() + 60 * 60 * 1000);
        let timeTextDisplay = formatEventDateTime(ev.start_time, ev.end_time);

        // Lấy mã màu trực tiếp từ typeColors (VD: '#10b981')
        let eventTypeKey = ev.type || ev.event_type;
        let colorCode = (typeColors && eventTypeKey && typeColors[eventTypeKey])
            ? typeColors[eventTypeKey]
            : '#0284c7'; // Màu mặc định nếu không khớp type nào

        // Kiểm tra xem sự kiện có rơi vào ngày hiện tại đang xét trên cột không
        weekDays.forEach((d, colIndex) => {
            let dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            let dayStart = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0, 0);
            let dayEnd = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59, 999);

            // Nếu khoảng thời gian sự kiện giao với ngày trong tuần
            if (evEnd >= dayStart && evStart <= dayEnd) {
                let actualStart = evStart < dayStart ? dayStart : evStart;
                let actualEnd = evEnd > dayEnd ? dayEnd : evEnd;
                let startHour = actualStart.getHours();
                let startMinute = actualStart.getMinutes();
                let endTotalMinutes = actualEnd.getHours() * 60 + actualEnd.getMinutes();
                let evStartDateStr = `${evStart.getFullYear()}-${String(evStart.getMonth() + 1).padStart(2, '0')}-${String(evStart.getDate()).padStart(2, '0')}`;

                if (evEnd > dayEnd && dateStr === evStartDateStr) {
                    endTotalMinutes = 24 * 60; // Kéo dài đến hết ngày nếu sự kiện kéo sang ngày hôm sau
                }

                let startTotalMinutes = startHour * 60 + startMinute;
                let durationMinutes = endTotalMinutes - startTotalMinutes;
                if (durationMinutes < 15) durationMinutes = 15; // Đảm bảo chiều cao tối thiểu cho dễ nhìn

                // Lấy ra ô chứa theo đúng cột ngày và giờ bắt đầu của sự kiện
                let targetCell = dayCellMatrix[colIndex] ? dayCellMatrix[colIndex][startHour] : null;
                if (!targetCell) return;

                // Tính toán vị trí hiển thị tuyệt đối (top) và chiều cao (height) theo pixel
                let topPx = (startMinute / 60) * rowHeight;
                let heightPx = (durationMinutes / 60) * rowHeight - 2;

                // Tạo thẻ hiển thị sự kiện trên lịch (Có bổ sung cursor: pointer và hiệu ứng hover)
                let eventCard = document.createElement('div');
                eventCard.style.cssText = `position: absolute; top: ${topPx}px; left: 2px; right: 2px; height: ${heightPx}px; background: ${colorCode}20; border-left: 3px solid ${colorCode}; padding: 4px 6px; border-radius: 4px; font-size: 11px; overflow: hidden; z-index: 5; box-shadow: 0 1px 2px rgba(0,0,0,0.05); box-sizing: border-box; cursor: pointer; transition: filter 0.15s ease, transform 0.15s ease;`;

                eventCard.innerHTML = `
                    <strong style="color: ${colorCode}; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; pointer-events: none;">${ev.title}</strong>
                    <span style="color: #64748b; font-size: 10px; pointer-events: none;">${timeTextDisplay}</span>
                `;

                // Thêm hiệu ứng hover sáng lên khi rê chuột vào sự kiện
                eventCard.addEventListener('mouseenter', () => {
                    eventCard.style.filter = 'brightness(0.92)';
                    eventCard.style.transform = 'translateY(-1px)';
                });
                eventCard.addEventListener('mouseleave', () => {
                    eventCard.style.filter = 'none';
                    eventCard.style.transform = 'translateY(0)';
                });

                // Gắn sự kiện click để mở form chỉnh sửa hoặc xử lý tương tác
                eventCard.addEventListener('click', (e) => {
                    e.stopPropagation(); // Ngăn sự kiện nổi bọt lên cell nền
                    if (typeof onEventClick === 'function') {
                        onEventClick(ev); // Gọi hàm callback chỉnh sửa sự kiện truyền vào
                    } else {
                        console.log('Đã nhấn vào sự kiện:', ev);
                    }
                });

                targetCell.appendChild(eventCard);
            }
        });
    });
}

/**
 * Hàm khởi tạo các sự kiện tương tác điều hướng tuần (Nút qua tuần trước, tuần tới, và quay về tuần hiện tại).
 * @param {Function} onNavigate - Callback được gọi sau khi chuyển tuần để render lại giao diện
 */
export function initWeeklyCalendarNav(onNavigate) {
    // Sự kiện bấm nút lùi lại tuần trước (-7 ngày)
    document.getElementById('weekPrevBtn')?.addEventListener('click', () => {
        currentWeekStartDate.setDate(currentWeekStartDate.getDate() - 7);
        onNavigate();
    });

    // Sự kiện bấm nút tiến tới tuần sau (+7 ngày)
    document.getElementById('weekNextBtn')?.addEventListener('click', () => {
        currentWeekStartDate.setDate(currentWeekStartDate.getDate() + 7);
        onNavigate();
    });

    // Sự kiện bấm nút "Hôm nay" để reset lịch về tuần chứa ngày hiện tại của hệ thống
    document.getElementById('weekTodayBtn')?.addEventListener('click', () => {
        let d = new Date();
        let day = d.getDay();
        let diff = d.getDate() - day + (day === 0 ? -6 : 1);
        currentWeekStartDate = new Date(d.setDate(diff));
        currentWeekStartDate.setHours(0, 0, 0, 0);
        onNavigate();
    });
}
