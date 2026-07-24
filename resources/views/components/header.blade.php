<!-- Phần header của trang quản trị, chứa tiêu đề, các nút tab bộ lọc và khu vực hiển thị trạng thái hệ thống -->
<header class="canvas-header">
    <div class="header-left">
        <h2 class="page-title">Sunburst Dashboard</h2>
        <div class="filter-tabs">
            <button class="tab-btn active">Server</button>
            <button class="tab-btn">Overview</button>
        </div>
    </div>
    <div class="header-right" style="display: flex; align-items: center; gap: 16px;">
        <!-- Nhúng component hiển thị thông báo hệ thống/cá nhân đã được tách gọn gàng -->
        @include('components.notification')
        <div class="status-indicator">
            <span class="pulse-dot"></span> API Active
        </div>
    </div>
</header>
