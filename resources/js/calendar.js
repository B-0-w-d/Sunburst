document.addEventListener('alpine:init', () => {
    Alpine.data('calendarComponent', (config) => ({
        mode: config.mode || 'full',
        currentDate: new Date(),
        currentMonthYear: '',
        isLoading: false,
        weekDays: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
        calendarDays: [],
        events: [],

        // --- STATE DÀNH CHO MODAL CHI TIẾT NGÀY ---
        showDayModal: false,
        selectedDateFormatted: '',
        selectedDateFull: '',
        selectedDayEvents: [],

        init() {
            this.updateCalendar();
            this.fetchEvents();
        },

        updateCalendar() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();

            // Format tiêu đề tháng/năm
            this.currentMonthYear = `Tháng ${month + 1}, ${year}`;

            // Lấy ngày đầu tiên của tháng
            const firstDayIndex = new Date(year, month, 1).getDay();
            // Số ngày trong tháng hiện tại
            const lastDay = new Date(year, month + 1, 0).getDate();
            // Số ngày của tháng trước
            const prevLastDay = new Date(year, month, 0).getDate();

            let days = [];

            // 1. Các ngày của tháng trước (lấp đầy tuần)
            for (let i = firstDayIndex; i > 0; i--) {
                let d = new Date(year, month - 1, prevLastDay - i + 1);
                days.push({
                    dayNumber: prevLastDay - i + 1,
                    fullDate: d.toISOString().split('T')[0],
                    isCurrentMonth: false,
                    isToday: this.isTodayCheck(d),
                    events: []
                });
            }

            // 2. Các ngày trong tháng hiện tại
            for (let i = 1; i <= lastDay; i++) {
                let d = new Date(year, month, i);
                days.push({
                    dayNumber: i,
                    fullDate: d.toISOString().split('T')[0],
                    isCurrentMonth: true,
                    isToday: this.isTodayCheck(d),
                    events: []
                });
            }

            // 3. Các ngày của tháng sau (lấp đầy lưới 35 hoặc 42 ô)
            const totalCells = days.length <= 35 ? 35 : 42;
            const nextDaysCount = totalCells - days.length;
            for (let i = 1; i <= nextDaysCount; i++) {
                let d = new Date(year, month + 1, i);
                days.push({
                    dayNumber: i,
                    fullDate: d.toISOString().split('T')[0],
                    isCurrentMonth: false,
                    isToday: this.isTodayCheck(d),
                    events: []
                });
            }

            this.calendarDays = days;
            this.mapEventsToDays();
        },

        isTodayCheck(date) {
            const today = new Date();
            return date.getDate() === today.getDate() &&
                   date.getMonth() === today.getMonth() &&
                   date.getFullYear() === today.getFullYear();
        },

        prevMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.updateCalendar();
            this.fetchEvents();
        },

        nextMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.updateCalendar();
            this.fetchEvents();
        },

        today() {
            this.currentDate = new Date();
            this.updateCalendar();
            this.fetchEvents();
        },

        fetchEvents() {
            this.isLoading = true;
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth() + 1;

            const token = localStorage.getItem('access_token');

            fetch(`/api/calendar?year=${year}&month=${month}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    this.events = data.data || [];
                    this.mapEventsToDays();
                }
                this.isLoading = false;
            })
            .catch(err => {
                console.error('Lỗi tải sự kiện:', err);
                this.isLoading = false;
            });
        },

        mapEventsToDays() {
            this.calendarDays.forEach(day => {
                let matchedEvents = this.events.filter(evt => {
                    let rawDate = evt.start_time || evt.start_date || evt.date;
                    if (!rawDate) return false;

                    let dateObj = new Date(rawDate);
                    let year = dateObj.getFullYear();
                    let month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    let dt = String(dateObj.getDate()).padStart(2, '0');
                    let evtLocalDate = `${year}-${month}-${dt}`;

                    return evtLocalDate === day.fullDate;
                });

                const uniqueEventsMap = new Map();
                matchedEvents.forEach(evt => {
                    const eventId = evt._id || evt.id || evt.title;
                    uniqueEventsMap.set(eventId, evt);
                });

                day.events = Array.from(uniqueEventsMap.values());
            });
        },

        getEventColor(type) {
            switch (type) {
                case 'show': return '#3b82f6';
                case 'practice': return '#10b981';
                case 'meeting': return '#f59e0b';
                case 'event': return '#ec4899';
                default: return '#6366f1';
            }
        },

        // --- CÁC HÀM TƯƠNG TÁC MODAL CHI TIẾT NGÀY & EVENT ---
        openDayDetail(date) {
            this.selectedDateFull = date.fullDate;
            this.selectedDateFormatted = `${date.dayNumber}/${this.currentDate.getMonth() + 1}/${this.currentDate.getFullYear()}`;
            this.selectedDayEvents = date.events || [];
            this.showDayModal = true;
        },

        selectEvent(evt) {
            const eventDate = evt.start_time || evt.start_date || evt.date;
            if (eventDate) {
                const dateObj = new Date(eventDate);
                const fullDateStr = dateObj.toISOString().split('T')[0];
                const dayCell = this.calendarDays.find(d => d.fullDate === fullDateStr);
                if (dayCell) {
                    this.openDayDetail(dayCell);
                }
            }
        },

        addEventForDate(dateStr) {
            this.showDayModal = false;
            window.dispatchEvent(new CustomEvent('open-add-event-modal', { detail: { date: dateStr } }));
        },

        editEvent(evt) {
            this.showDayModal = false;
            window.dispatchEvent(new CustomEvent('open-edit-event-modal', { detail: { event: evt } }));
        },

        deleteEvent(evt) {
            if (!confirm(`Bạn có chắc chắn muốn xóa sự kiện "${evt.title}"?`)) return;

            const token = localStorage.getItem('access_token');
            const eventId = evt._id || evt.id;

            fetch(`/api/calendar/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.events = this.events.filter(e => (e._id || e.id) !== eventId);
                    this.mapEventsToDays();
                    this.selectedDayEvents = this.selectedDayEvents.filter(e => (e._id || e.id) !== eventId);
                    if (this.selectedDayEvents.length === 0) {
                        this.showDayModal = false;
                    }
                } else {
                    alert(data.message || 'Xóa sự kiện thất bại!');
                }
            })
            .catch(err => {
                console.error('Lỗi khi xóa sự kiện:', err);
                alert('Có lỗi xảy ra khi xóa sự kiện.');
            });
        }
    }));
});
