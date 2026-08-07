{{-- Gọi component navbar tùy chỉnh với tiêu đề "Overview - Sunburst" --}}
<x-navbar title="Overview - Sunburst">
    <!-- Khung bọc giao diện trang tổng quan (overview) -->
    <div class="nav-view-wrapper" style="position: relative; background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">

        <!-- Lớp phủ tối và làm mờ (backdrop overlay) -->
        <div style="position: absolute; inset: 0; background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 1;"></div>

        <!-- Khối chứa nội dung bên trong trang overview -->
        <div class="content-container" style="position: relative; z-index: 2; ">
            <div class="parent">

                <!-- Ô số 1 -->
                <div class="content-card div1">
                    <!-- Lịch tháng nhỏ điều hướng -->
                    <div id="miniCalendarWidget" class="mini-calendar-widget" style="background-color: transparent; border: none">
                        <div class="mini-calendar-header">
                            <h4 id="miniCalendarMonthYear" class="mini-calendar-title">Tháng 8, 2026</h4>
                            <div class="mini-calendar-nav-btns">
                                <button type="button" id="miniPrevBtn" class="mini-cal-btn">‹</button>
                                <button type="button" id="miniNextBtn" class="mini-cal-btn">›</button>
                            </div>
                        </div>
                        <div class="mini-calendar-days">
                            <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                        </div>
                        <div id="miniCalendarGrid" class="mini-calendar-grid">
                            <!-- Render bằng JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Ô số 3 -->
                <div class="content-card div3">
                    <div style="padding: 16px; display: flex; justify-content: center; align-items: center; height: 100%;">
                        <p style="color: #64748b; font-size: 13px; margin: 0;">3</p>
                    </div>
                </div>

                <!-- Ô số 7: CHỨA WIDGET THÔNG BÁO MỚI (Đã gán CSS Class) -->
                <div class="content-card div7">
                    <div x-data="{
                        unreadCount: 0,
                        notifications: [],
                        init() {
                            this.fetchData();
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
                            .then(() => { window.location.href = '/calendar'; })
                            .catch(() => { window.location.href = '/calendar'; });
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
                    }" class="">

                        <!-- Header cố định không bị cuộn -->
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

                        <!-- Phần danh sách có thanh cuộn dọc (Scrollable) -->
                        <div class="dropdown-list">
                            <template x-for="item in notifications" :key="item._id || item.id">
                                <div @click="markAsRead(item._id || item.id)"
                                     class="dropdown-item"
                                     :class="{ 'unread-card': !item.read_at }">
                                    <div style="display: flex; align-items: flex-start; gap: 8px; width: 100%;">
                                        <template x-if="!item.read_at">
                                            <span class="item-unread-dot"></span>
                                        </template>
                                        <div class="item-content">
                                            <p class="item-title" x-text="item.title"></p>
                                            <p class="item-message" x-text="item.message"></p>
                                            <span class="item-time" x-text="item.created_at ? new Date(item.created_at).toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' }) : ''"></span>
                                        </div>
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

                <!-- Ô số 6 -->
                <div class="content-card div6">
                    <x-miniPoll />
                </div>

                <!-- Ô số 4 -->
                <div class="content-card div4">
                    <div style="padding: 16px; display: flex; justify-content: center; align-items: center; height: 100%;">
                        <p style="color: #64748b; font-size: 13px; margin: 0;">4</p>
                    </div>
                </div>

                <!-- Ô số 8 -->
                <div class="content-card div8">
                    <div style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; height: 100%; box-sizing: border-box; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
                        <!-- Hàng trên: Tiêu đề widget và Badge trạng thái -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 14px; font-weight: 700; color: #1e293b; letter-spacing: -0.2px;">Sự Kiện gần nhất:</span>
                            <span id="widgetStatusBadge" style="display: none;"></span>
                        </div>

                        <!-- Khung chứa nội dung động do JavaScript điều khiển -->
                        <div id="widgetEventContent" style="flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
                            <p style="font-size: 13px; color: #94a3b8; font-style: italic; margin: 0; text-align: center;">Đang tải sự kiện...</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-navbar>

{{-- Sử dụng @push('scripts') để đẩy đoạn script cấu hình modal vào stack scripts của layout chính --}}
@push('scripts')
<script>
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('is-open');
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('is-open');
    };
</script>
@endpush
