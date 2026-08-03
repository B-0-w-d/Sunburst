<div x-data="{
    // Khởi tạo danh sách các khảo sát đang mở
    polls: [],

    // Định nghĩa bảng màu tương ứng với từng loại sự kiện
    typeColors: {
        'MEETING': '#3b82f6',
        'EVENT': '#10b981',
        'TASK': '#f59e0b',
        'POLL': '#8b5cf6',
        'DEFAULT': '#64748b'
    },

    // Hàm lấy màu sắc an toàn dựa vào loại sự kiện (trả về màu mặc định nếu không khớp)
    getTypeColor(type) {
        return this.typeColors[type] || this.typeColors['DEFAULT'];
    },

    // Hàm khởi chạy: Tự động gọi API lấy danh sách sự kiện có trạng thái là POLL khi component được load
    init() {
        fetch('/api/calendar?status=POLL', {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('access_token')
            }
        })
        .then(res => res.json())
        .then(data => {
            const list = data.data || data;
            this.polls = Array.isArray(list) ? list : [];
        })
        .catch(err => console.error('Lỗi tải khảo sát:', err));
    },

    // Điều hướng người dùng tới trang lịch chi tiết kèm theo ID của khảo sát cần mở
    goToCalendar(eventId) {
        window.location.href = `/calendar?open_poll=${eventId}`;
    },

    // Xử lý và định dạng khoảng thời gian khảo sát (Từ ngày - Đến ngày)
    formatDateRange(item) {
        let config = item.poll_config || item.config;
        if (typeof config === 'string') {
            try { config = JSON.parse(config); } catch (e) { config = null; }
        }
        if (!config || !config.start_date || !config.end_date) return '';

        const formatDate = (dateStr) => {
            const cleanStr = dateStr.split('T')[0];
            const [y, m, d] = cleanStr.split('-');
            return `${d}/${m}/${y}`;
        };
        return `${formatDate(config.start_date)} - ${formatDate(config.end_date)}`;
    },

    // Xử lý và định dạng thời hạn chót (Deadline) của khảo sát
    formatDeadline(item) {
        let config = item.poll_config || item.config;
        if (typeof config === 'string') {
            try { config = JSON.parse(config); } catch (e) { config = null; }
        }
        if (!config || !config.deadline) return '';

        try {
            const parts = config.deadline.split(' ');
            const dateParts = parts[0].split('-');
            const timeParts = parts[1] ? parts[1].split(':') : ['00', '00'];

            const day = dateParts[2];
            const month = dateParts[1];
            const year = dateParts[0];
            const hour = timeParts[0];
            const minute = timeParts[1];

            return `${hour}:${minute} - ${day}/${month}/${year}`;
        } catch (e) {
            return config.deadline;
        }
    }
}" style="padding: 16px; display: flex; flex-direction: column; height: 100%; box-sizing: border-box; min-height: 0;">

    <!-- Header cố định hiển thị tiêu đề và số lượng khảo sát -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <span class="nav-dot" style="background-color: #3b82f6; width: 6px; height: 6px; border-radius: 50%; display: inline-block;"></span>
            <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0;">Khảo Sát Đang Mở</h3>
            <!-- Hiển thị huy hiệu số lượng khảo sát nếu có nhiều hơn 1 -->
            <template x-if="polls.length > 1">
                <span style="background: #3b82f6; color: white; font-size: 10px; font-weight: 600; padding: 2px 5px; border-radius: 9999px;" x-text="polls.length"></span>
            </template>
        </div>
        <a href="/calendar" style="font-size: 11px; color: #2563eb; text-decoration: none; font-weight: 600;">Xem tất cả</a>
    </div>

    <!-- Phần danh sách khảo sát có thanh cuộn dọc (Scrollable) -->
    <div style="flex-grow: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; min-height: 0; padding-right: 4px;">

        <!-- Vòng lặp hiển thị từng khảo sát -->
        <template x-for="item in polls" :key="item._id || item.id">
            <div @click="goToCalendar(item._id || item.id)"
                 style="padding: 10px 12px; border-radius: 8px; border: 1px solid #f1f5f9; background: #ffffff; cursor: pointer; transition: all 0.2s;"
                 onmouseover="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#f8fafc';"
                 onmouseout="this.style.borderColor='#f1f5f9'; this.style.backgroundColor='#ffffff';">

                <!-- Phần nhãn loại sự kiện và trạng thái đã điền lịch -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                    <span :style="`font-size: 10px; background: ${getTypeColor(item.type)}20; color: ${getTypeColor(item.type)}; padding: 1px 5px; border-radius: 4px; font-weight: 600;`" x-text="item.type || 'POLL'"></span>
                    <template x-if="item.has_submitted_availability">
                        <span style="font-size: 10px; color: #059669; font-weight: 600; display: flex; align-items: center; gap: 2px;">✓ Đã điền</span>
                    </template>
                </div>

                <!-- Tiêu đề khảo sát -->
                <p style="font-size: 12px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0;" x-text="item.title"></p>

                <!-- Hiển thị thời gian diễn ra khảo sát -->
                <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: #64748b; margin-bottom: 3px;">
                    <span>📅</span>
                    <span x-text="formatDateRange(item)"></span>
                </div>

                <!-- Trường hợp có thiết lập hạn chót (Deadline) -->
                <template x-if="formatDeadline(item)">
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: #dc2626; font-weight: 500;">
                        <span>⏰</span>
                        <span>Hạn chót: <strong x-text="formatDeadline(item)"></strong></span>
                    </div>
                </template>

                <!-- Trường hợp không có deadline (Không giới hạn thời gian phản hồi) -->
                <template x-if="!formatDeadline(item)">
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: #059669; font-weight: 500;">
                        <span>⏰</span>
                        <span>Hạn chót: <strong>Không giới hạn</strong></span>
                    </div>
                </template>
            </div>
        </template>

        <!-- Trạng thái thông báo khi danh sách khảo sát trống -->
        <template x-if="polls.length === 0">
            <p style="padding: 16px; text-align: center; color: #94a3b8; font-size: 12px; margin: auto;">Không có khảo sát nào đang mở.</p>
        </template>
    </div>
</div>
