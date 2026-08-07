<div class="notification-wrapper" x-data="{
    open: false,
    unreadCount: 0,
    notifications: [],

    init() {
        this.fetchData();
    },

    toggleDropdown() {
        this.open = !this.open;
    },

    fetchData() {
        fetch('/api/notifications', {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('access_token')
            }
        })
        .then(res => res.json())
        .then(data => {
            const list = data.notifications || data.data || data;
            this.notifications = Array.isArray(list) ? list : [];
            this.unreadCount = data.unread_count !== undefined
                ? data.unread_count
                : this.notifications.filter(item => !item.read_at).length;
        })
        .catch(error => console.error('Lỗi tải thông báo:', error));
    },

    markAsRead(id) {
        fetch(`/api/notifications/${id}/read`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('access_token')
            }
        })
        .then(() => {
            window.location.href = '/calendar';
        })
        .catch(() => {
            window.location.href = '/calendar';
        });
    },

    markAllAsRead() {
        fetch('/api/notifications/read-all', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('access_token')
            }
        }).then(() => this.fetchData());
    }
}">
    <!-- 1. ICON CHUÔNG TRÊN HEADER -->
    <button @click.stop="toggleDropdown" type="button" class="notification-bell-btn" aria-label="Thông báo">
        <svg class="bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Chấm đỏ nhỏ trên icon chuông -->
        <template x-if="unreadCount > 0">
            <span class="unread-dot-badge"></span>
        </template>
    </button>

    <!-- 2. KHUNG DROPDOWN HIỂN THỊ DANH SÁCH THÔNG BÁO -->
    <div x-show="open" @click.outside="open = false" x-cloak class="notification-dropdown">
        <div class="dropdown-header">
            <div class="dropdown-title-wrapper">
                <span class="dropdown-title">Thông báo</span>
                <template x-if="unreadCount > 0">
                    <span class="unread-count-pill" x-text="unreadCount + ' mới'"></span>
                </template>
            </div>
            <template x-if="unreadCount > 0">
                <button @click="markAllAsRead" type="button" class="text-link">Đọc tất cả</button>
            </template>
        </div>

        <div class="dropdown-list">
            <!-- Vòng lặp hiển thị danh sách -->
            <template x-for="item in notifications" :key="item._id || item.id">
                <div @click="markAsRead(item._id || item.id)" class="dropdown-item" :class="{ 'unread-bg': !item.read_at }">
                    <!-- Dấu chấm xanh cho thông báo chưa đọc (đã tinh chỉnh thẳng hàng) -->
                    <div style="width: 8px; flex-shrink: 0; display: flex; justify-content: center;">
                        <template x-if="!item.read_at">
                            <span class="item-unread-dot"></span>
                        </template>
                    </div>

                    <div class="item-content">
                        <p class="item-title" x-text="item.title"></p>
                        <p class="item-message" x-text="item.message"></p>
                        <!-- Chỉ hiển thị một định dạng thời gian chuẩn gọn gàng, loại bỏ việc bị lặp -->
                        <span class="item-time">
                            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span x-text="item.created_at ? new Date(item.created_at).toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) : ''"></span>
                        </span>
                    </div>
                </div>
            </template>

            <!-- Trạng thái khi không có thông báo -->
            <template x-if="notifications.length === 0">
                <div class="empty-state">
                    <p class="empty-text">Hiện tại không có thông báo nào.</p>
                </div>
            </template>
        </div>
    </div>
</div>
