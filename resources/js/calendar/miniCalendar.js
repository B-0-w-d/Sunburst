// =========================================================================
// QUẢN LÝ LỊCH THÁNG MINI (SIDEBAR)
// =========================================================================

import { typeColors } from './calendar.js';
export let currentMiniMonthDate = new Date();

export function renderMiniCalendar(cachedConfirmedEvents, onDateClick) {
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

    for (let d = 1; d <= totalDays; d++) {
        let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        let isToday = dateStr === todayStr;

        // LẤY TẤT CẢ CÁC SỰ KIỆN TRONG NGÀY (thay vì chỉ dùng find lấy 1 cái)
        let dayEvents = cachedConfirmedEvents.filter(ev => {
            if (!ev.start_time) return false;
            const cleanStart = ev.start_time.replace(/\.\d+([+-]\d{2}:\d{2}|Z)?$/, '').replace('T', ' ');
            return cleanStart.startsWith(dateStr);
        });

        let style = `padding: 4px 0; text-align: center; border-radius: 4px; cursor: pointer; position: relative; font-size: 12px;`;

        if (isToday) {
            style += ` background: #3b82f6; color: #fff; font-weight: bold;`;
        } else if (dayEvents.length > 0) {
            // Lấy màu của sự kiện đầu tiên làm màu nền cho ô ngày, hoặc dùng màu mặc định
            const eventTypeKey = (dayEvents[0].type || '').toUpperCase();
            let bgColor = typeColors[eventTypeKey] || '#3b82f6';
            style += ` background: ${bgColor}; color: #fff; font-weight: bold;`;
        } else {
            style += ` color: #334155;`;
        }

        let cell = document.createElement('div');
        cell.className = 'mini-day-cell';
        cell.style.cssText = style;
        cell.innerHTML = `${d}`;
        cell.dataset.date = dateStr;

        // Lưu danh sách sự kiện trực tiếp vào dataset để hiển thị tooltip nhanh
        if (dayEvents.length > 0) {
            cell.dataset.events = JSON.stringify(dayEvents.map(ev => ({
                title: ev.title,
                type: ev.type || 'N/A'
            })));
        }

        grid.appendChild(cell);
    }

    // Xử lý hiển thị danh sách sự kiện khi rê chuột hoặc click vào ngày
    // Tạo sẵn một element tooltip nhỏ nếu chưa có trên giao diện
    let tooltip = document.getElementById('miniCalendarTooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'miniCalendarTooltip';
        tooltip.style.cssText = `position: absolute; background: #1e293b; color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 11px; z-index: 9999; display: none; pointer-events: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 180px;`;
        document.body.appendChild(tooltip);
    }

    grid.querySelectorAll('.mini-day-cell').forEach(cell => {
        // Hiện danh sách sự kiện khi rê chuột vào
        cell.addEventListener('mouseenter', function(e) {
            let eventsAttr = this.dataset.events;
            if (!eventsAttr) return;
            let events = JSON.parse(eventsAttr);

            let htmlList = events.map(ev => `• <b>[${ev.type}]</b> ${ev.title}`).join('<br>');
            tooltip.innerHTML = htmlList;
            tooltip.style.display = 'block';

            let rect = this.getBoundingClientRect();
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 6 + window.scrollY}px`;
            tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + window.scrollX}px`;
        });

        // Ẩn tooltip khi rê chuột ra
        cell.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });

        // Giữ nguyên logic click chuyển lịch tuần như cũ
        cell.addEventListener('click', function() {
            if (typeof onDateClick === 'function') {
                onDateClick(this.getAttribute('data-date'));
            }
        });
    });
}

export function initMiniCalendarNav(onChange) {
    document.getElementById('miniPrevBtn')?.addEventListener('click', () => {
        currentMiniMonthDate.setMonth(currentMiniMonthDate.getMonth() - 1);
        onChange();
    });
    document.getElementById('miniNextBtn')?.addEventListener('click', () => {
        currentMiniMonthDate.setMonth(currentMiniMonthDate.getMonth() + 1);
        onChange();
    });
}
