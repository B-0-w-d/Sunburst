<!-- Khối điều hướng và tiêu đề đầu trang của trang quản trị (canvas-header) -->
<header class="canvas-header">
    <!-- Phần bên trái header: hiển thị tên bảng điều khiển và các nút tab chuyển đổi giao diện -->
    <div class="header-left">
        <!-- Tiêu đề chính định danh trang quản trị Sunburst -->
        <div class="content-header" style="padding-top: 20px;">
            <div>
                <h1 class="content-title">Xin chào, {{ auth()->user()->name }}</h1>

                <p class="content-subtitle">Chào mừng bạn đến với hệ thống quản lý Sunburst!</p>
            </div>
        </div>
        <!-- Bộ lọc dạng tab dùng để chuyển đổi giữa các góc nhìn hiển thị (Server hoặc Overview) -->
    </div>
    <!-- Phần bên phải header: căn chỉnh bố cục dạng Flexbox để chứa chuông thông báo và trạng thái kết nối -->
    <div class="header-right" style="display: flex; align-items: center; gap: 16px;">
        <!-- Tích hợp sub-view Blade chứa giao diện component thông báo của hệ thống hoặc cá nhân -->
        @include('components.notification')
        <!-- Khối hiển thị nhãn trạng thái hoạt động của hệ thống kèm theo hiệu ứng chấm tròn nhấp nháy -->
        <div class="status-indicator">
            <!-- Chấm tròn tạo hiệu ứng chớp nháy (pulse) biểu thị tín hiệu kết nối -->
            <span class="pulse-dot"></span> API Active
        </div>
    </div>
</header>
