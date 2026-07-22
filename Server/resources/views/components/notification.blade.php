<!-- 1. Tải thư viện Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- 2. Nhúng file notification.js của bạn vào -->
    <script src="{{ asset('js/notification.js') }}"></script>
    {{-- Hoặc nếu dùng Vite thì khai báo qua @vite hoặc @stack --}}

    @stack('scripts')
<div class="notification-wrapper" x-data="notificationDropdown()">
    <!-- 1. ICON CHUÔNG TRÊN HEADER -->
    <button @click.stop="toggleDropdown" type="button" class="notification-bell-btn">
        <svg class="bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Chấm đỏ báo có thông báo chưa đọc -->
        <template x-if="unreadCount > 0">
            <span class="unread-dot-badge"></span>
        </template>
    </button>

    <!-- Dropdown hiển thị trực tiếp bằng x-show trên div, dùng @click.outside để bấm ra ngoài tự đóng -->
    <div x-show="open" @click.outside="open = false" x-cloak class="notification-dropdown">
        <div class="dropdown-header">
            <span class="dropdown-title">Thông báo gần đây</span>
            <button @click="markAllAsRead" type="button" class="text-link">Đọc tất cả</button>
        </div>
        <div class="dropdown-list">
            <template x-for="item in notifications" :key="item._id">
                <div @click="markAsRead(item._id)" class="dropdown-item" :class="{ 'unread-bg': !item.read_at }">
                    <template x-if="!item.read_at">
                        <span class="item-unread-dot"></span>
                    </template>
                    <div class="item-content">
                        <p class="item-title" x-text="item.title"></p>
                        <p class="item-message" x-text="item.message"></p>
                        <span class="item-time" x-text="new Date(item.created_at).toLocaleString()"></span>
                    </div>
                </div>
            </template>
            <template x-if="notifications.length === 0">
                <p class="empty-text">Không có thông báo nào.</p>
            </template>
        </div>
    </div>
</div>
