// =========================================================================
// QUẢN LÝ LỊCH THÁNG MINI (SIDEBAR)
// =========================================================================

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

    const typeColors = {
        'PRACTICE': '#10b981',
        'SHOW': '#ef4444',
        'MEETING': '#f59e0b'
    };

    for (let d = 1; d <= totalDays; d++) {
        let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        let isToday = dateStr === todayStr;

        let dayEvent = cachedConfirmedEvents.find(ev => {
            if (!ev.start_time) return false;
            const cleanStart = ev.start_time.replace(/\.\d+([+-]\d{2}:\d{2}|Z)?$/, '').replace('T', ' ');
            return cleanStart.startsWith(dateStr);
        });

        let style = `padding: 4px 0; text-align: center; border-radius: 4px; cursor: pointer; position: relative; font-size: 12px;`;

        if (isToday) {
            style += ` background: #3b82f6; color: #fff; font-weight: bold;`;
        } else if (dayEvent) {
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
