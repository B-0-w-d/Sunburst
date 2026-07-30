<!DOCTYPE html>
{{-- Khai báo ngôn ngữ trang web dựa trên cấu hình locale của ứng dụng Laravel --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Token CSRF bảo mật cho các request phương thức POST/PUT/DELETE --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tiêu đề động của trang web, mặc định là 'Sunburst' nếu biến $title không được truyền vào --}}
    <title>{{ $title ?? 'Sunburst' }}</title>

    <!-- Kết nối font chữ Plus Jakarta Sans từ Google Fonts/Bunny -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus+jakarta+sans:300,400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Tích hợp các file tài nguyên CSS và JS chính thông qua Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <!-- Tải thư viện Alpine.js phục vụ cho các tương tác giao diện động nhẹ -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</head>

<body>

    <!-- Khung chứa giao diện chính của toàn web -->
    <div class="dashboard-container">
        <!-- Thanh điều hướng dạng icon bên trái (sidebar thu gọn) -->
        <aside class="icon-strip">
            <!-- Logo thương hiệu Sunburst -->
            <img src="{{ asset('images/SunburstLogo.png') }}" width="60" height="60" alt="Logo">
            <!-- Khu vực chứa các liên kết điều hướng chính, tự động bật trạng thái active theo URL hiện tại -->
            <nav class="icon-nav">
                <!-- Nút chuyển về trang chủ, kiểm tra route '/' -->
                <a href="/" class="icon-link {{ request()->is('/') ? 'active' : '' }}"><x-icons.house /></a>
                <!-- Nút chuyển sang trang lịch trình -->
                <a href="/calendar" class="icon-link"><x-icons.calendar /></a>
                <!-- Nút chuyển sang trang quản lý thành viên, kiểm tra URL bắt đầu bằng 'members' -->
                <a href="/members" class="icon-link {{ request()->is('members*') ? 'active' : '' }}"><x-icons.memberGroup /></a>
                <!-- Nút đường dẫn phụ / yêu thích -->
                <a href="#" class="icon-link"><x-icons.heart /></a>
            </nav>

            <!-- Khu vực hiển thị thông tin profile người dùng ở góc dưới thanh sidebar -->
            <div class="user-profile-container">
                <!-- Avatar hiển thị chữ cái đầu tên người dùng hoặc icon mặc định khi chưa đăng nhập -->
                <div class="user-profile">
                    @auth
                        {{ substr(auth()->user()->name, 0, 1) }}
                    @else
                        <svg ...></svg>
                    @endauth
                </div>

                <!-- Menu thả xuống (dropdown) chứa thông tin chi tiết và tùy chọn tài khoản -->
                <div class="profile-dropdown">
                    @auth
                        <!-- Hiển thị tên và email của tài khoản đang đăng nhập -->
                        <div class="dropdown-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-email">{{ auth()->user()->email }}</span>
                        </div>

                        <!-- Nút kích hoạt mở modal chỉnh sửa thông tin cá nhân -->
                        <button type="button"
                                onclick="openModal('editUserModal')"
                                class="dropdown-item"
                                style="background: none; border: none; width: 100%; text-align: center; cursor: pointer;">
                            Edit Profile
                        </button>

                        <!-- Form gửi yêu cầu đăng xuất (Logout) theo phương thức POST -->
                        <form action="/logout" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item">Log Out</button>
                        </form>
                    @endauth
                </div>
            </div>
        </aside>

        <!-- Khu vực không gian làm việc chính bên phải -->
        <div class="main-canvas">
            <!-- Nhúng component header chung của giao diện -->
            <x-header />
            <!-- Vùng chứa nội dung biến đổi linh hoạt theo từng trang cụ thể -->
            <main class="canvas-content">
                {{ $slot }}
            </main>
        </div>
    </div> <!-- Hết phần dashboard-container -->

    <!-- ĐẶT MODAL Ở NGOÀI CÙNG TẠI ĐÂY (NẰM SÁT TRƯỚC THẺ ĐÓNG BODY) -->
    <x-profile/>

    <!-- Đoạn mã script tự động bật lại modal chỉnh sửa profile nếu có lỗi validate dữ liệu trả về từ server -->
    @if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof openModal === 'function') {
                openModal('editProfileModal');
            }
        });
    </script>
    @endif

    @stack('scripts')
</body>

</html>
