<!-- Component hiển thị hệ thống thông báo sử dụng Alpine.js và tích hợp API bất đồng bộ -->
<div class="notification-wrapper" x-data="{
    open: false,        // Trạng thái đóng/mở của khung dropdown thông báo
    unreadCount: 0,     // Tổng số lượng thông báo chưa đọc (dùng cho chấm đỏ)
    notifications: [],  // Mảng chứa danh sách các thông báo lấy từ API

    // Hàm khởi tạo tự động chạy khi component được Alpine load lên
    init() {
        this.fetchData();
    },

    // Hàm chuyển đổi trạng thái ẩn/hiện của dropdown khi click vào icon chuông
    toggleDropdown() {
        this.open = !this.open;
    },

    // Hàm gọi API lấy danh sách thông báo và số lượng chưa đọc từ server
    fetchData() {
        fetch('/api/notifications', {
            headers: {
                'Content-Type': 'application/json',
                // Đính kèm token xác thực lấy từ localStorage của người dùng
                'Authorization': 'Bearer ' + localStorage.getItem('access_token')
            }
        })
        .then(res => res.json())
        .then(data => {
            console.log('Dữ liệu API trả về:', data); // In ra console để debug cấu trúc JSON

            // Hứng dữ liệu linh hoạt theo các cấu trúc trả về phổ biến của Laravel API
            const list = data.notifications || data.data || data;
            this.notifications = Array.isArray(list) ? list : [];

            // Cập nhật số lượng chưa đọc từ API hoặc tự lọc từ mảng thông báo nếu API không trả về sẵn
            this.unreadCount = data.unread_count !== undefined
                ? data.unread_count
                : this.notifications.filter(item => !item.read_at).length;
        })
        .catch(error => console.error('Lỗi tải thông báo:', error));
    },

    // Hàm gửi yêu cầu đánh dấu 1 thông báo cụ thể là đã đọc
    markAsRead(id) {
        fetch(`/api/notifications/${id}/read`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('access_token')
            }
        }).then(() => this.fetchData()); // Sau khi cập nhật thành công, gọi lại hàm fetchData để làm mới giao diện
    },

    // Hàm gửi yêu cầu đánh dấu tất cả thông báo là đã đọc
    markAllAsRead() {
        fetch('/api/notifications/read-all', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('access_token')
            }
        }).then(() => this.fetchData()); // Làm mới lại danh sách sau khi đã đọc tất cả
    }
}">
    <!-- 1. ICON CHUÔNG TRÊN HEADER -->
    <button @click.stop="toggleDropdown" type="button" class="notification-bell-btn">
        <svg class="bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Hiển thị chấm đỏ thông báo chưa đọc nếu số lượng lớn hơn 0 -->
        <template x-if="unreadCount > 0">
            <span class="unread-dot-badge"></span>
        </template>
    </button>

    <!-- 2. KHUNG DROPDOWN HIỂN THỊ DANH SÁCH THÔNG BÁO -->
    <!-- x-show: ẩn hiện theo biến open | @click.outside: tự động đóng khi click ra bên ngoài khung -->
    <div x-show="open" @click.outside="open = false" x-cloak class="notification-dropdown">
        <div class="dropdown-header">
            <span class="dropdown-title">Thông báo gần đây</span>
            <button @click="markAllAsRead" type="button" class="text-link">Đọc tất cả</button>
        </div>
        <div class="dropdown-list">
            <!-- Vòng lặp hiển thị danh sách thông báo, hỗ trợ nhận diện cả _id (MongoDB) hoặc id (MySQL) -->
            <template x-for="item in notifications" :key="item._id || item.id">
                <div @click="markAsRead(item._id || item.id)" class="dropdown-item" :class="{ 'unread-bg': !item.read_at }">
                    <!-- Chấm nhỏ màu xanh/đỏ biểu thị thông báo chưa đọc bên trong item -->
                    <template x-if="!item.read_at">
                        <span class="item-unread-dot"></span>
                    </template>
                    <div class="item-content">
                        <p class="item-title" x-text="item.title"></p>
                        <p class="item-message" x-text="item.message"></p>
                        <!-- Hiển thị thời gian định dạng chuẩn địa phương, an toàn với giá trị null -->
                        <span class="item-time" x-text="item.created_at ? new Date(item.created_at).toLocaleString() : ''"></span>
                    </div>
                </div>
            </template>

            <!-- Trạng thái hiển thị khi mảng thông báo rỗng -->
            <template x-if="notifications.length === 0">
                <p class="empty-text" style="padding: 12px; text-align: center; color: #6b7280;">Không có thông báo nào.</p>
            </template>
        </div>
    </div>
</div>
