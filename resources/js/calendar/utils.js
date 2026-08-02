// =========================================================================
// TIỆN ÍCH DÙNG CHUNG (UTILS) & CẤU HÌNH API LỊCH
// =========================================================================

export const token = localStorage.getItem('access_token');
export const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
};

export function formatEventDateTime(startIso, endIso) {
    if (!startIso) return '';
    try {
        // Hàm phụ tách chuỗi thô để tránh lệch múi giờ của trình duyệt
        const parseToParts = (isoStr) => {
            const cleanStr = isoStr.replace('Z', '').replace('T', ' ');
            const parts = cleanStr.split(' ');
            const datePart = parts[0] || '';
            const timePart = parts[1] || '00:00:00';
            const [y, m, d] = datePart.split('-').map(Number);
            const [h, min, s] = timePart.split(':').map(Number);
            return {
                year: y || 0,
                month: m || 1,
                day: d || 1,
                hours: h || 0,
                minutes: min || 0
            };
        };

        const pad = (n) => String(n).padStart(2, '0');
        const start = parseToParts(startIso);

        const startHours = pad(start.hours);
        const startMinutes = pad(start.minutes);
        const startDay = pad(start.day);
        const startMonth = pad(start.month);
        const startYear = start.year;

        if (!endIso) {
            return `${startHours}:${startMinutes} - ${startDay}/${startMonth}/${startYear}`;
        }

        const end = parseToParts(endIso);
        const endHours = pad(end.hours);
        const endMinutes = pad(end.minutes);
        const endDay = pad(end.day);
        const endMonth = pad(end.month);
        const endYear = end.year;

        const isSameDay = start.day === end.day && start.month === end.month && start.year === end.year;

        if (isSameDay) {
            return `${startHours}:${startMinutes} - ${endHours}:${endMinutes}, ${startDay}/${startMonth}/${startYear}`;
        } else {
            return `${startHours}:${startMinutes}, ${startDay}/${startMonth}/${startYear} - ${endHours}:${endMinutes}, ${endDay}/${endMonth}/${endYear}`;
        }
    } catch (e) {
        return startIso;
    }
}
