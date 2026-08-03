// =========================================================================
// TIỆN ÍCH DÙNG CHUNG (UTILS) & CẤU HÌNH API LỊCH
// =========================================================================

// Lấy token xác thực từ localStorage để gửi kèm trong các request API
export const token = localStorage.getItem('access_token');

// Cấu hình headers mặc định cho các yêu cầu HTTP (chứa token xác thực)
export const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
};

/**
 * Hàm phân tích chuỗi thời gian thô/ISO thành đối tượng Date cục bộ mà không bị lệch múi giờ UTC
 * @param {string} dateStr - Chuỗi thời gian (VD: "2026-08-03T08:30:00")
 * @returns {Date} Đối tượng Date theo giờ local
 */
export function parseLocalDateTime(dateStr) {
    if (!dateStr) return new Date();
    try {
        const cleanStr = dateStr.replace(/\.\d+([+-]\d{2}:\d{2}|Z)?$/, '').replace('Z', '').replace('T', ' ');
        const parts = cleanStr.split(' ');
        const datePart = parts[0] || '';
        const timePart = parts[1] || '00:00:00';

        const [y, m, d] = datePart.split('-').map(Number);
        const [h, min, s] = timePart.split(':').map(Number);

        return new Date(y || 1970, (m || 1) - 1, d || 1, h || 0, min || 0, s || 0);
    } catch (e) {
        return new Date();
    }
}

/**
 * Hàm định dạng chuỗi thời gian sự kiện (ISO string) thành định dạng dễ đọc cho người dùng Việt Nam
 * @param {string} startIso - Thời gian bắt đầu dạng chuỗi ISO hoặc thô
 * @param {string} endIso - Thời gian kết thúc dạng chuỗi ISO hoặc thô (tùy chọn)
 * @returns {string} Chuỗi thời gian đã được định dạng (VD: 08:30 - 11:30, 02/08/2026)
 */
export function formatEventDateTime(startIso, endIso) {
    if (!startIso) return '';
    try {
        const pad = (n) => String(n).padStart(2, '0');
        const start = parseLocalDateTime(startIso);

        const startHours = pad(start.getHours());
        const startMinutes = pad(start.getMinutes());
        const startDay = pad(start.getDate());
        const startMonth = pad(start.getMonth() + 1);
        const startYear = start.getFullYear();

        if (!endIso) {
            return `${startHours}:${startMinutes} - ${startDay}/${startMonth}/${startYear}`;
        }

        const end = parseLocalDateTime(endIso);
        const endHours = pad(end.getHours());
        const endMinutes = pad(end.getMinutes());
        const endDay = pad(end.getDate());
        const endMonth = pad(end.getMonth() + 1);
        const endYear = end.getFullYear();

        const isSameDay = start.getDate() === end.getDate() &&
                          start.getMonth() === end.getMonth() &&
                          start.getFullYear() === end.getFullYear();

        if (isSameDay) {
            return `${startHours}:${startMinutes} - ${endHours}:${endMinutes}, ${startDay}/${startMonth}/${startYear}`;
        } else {
            return `${startHours}:${startMinutes}, ${startDay}/${startMonth}/${startYear} - ${endHours}:${endMinutes}, ${endDay}/${endMonth}/${endYear}`;
        }
    } catch (e) {
        return startIso;
    }
}
