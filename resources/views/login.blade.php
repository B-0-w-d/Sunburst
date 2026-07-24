<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Thẻ meta cung cấp CSRF Token cho các yêu cầu AJAX/Fetch/Axios -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | Sunburst Dashboard</title>

    <!-- Nạp các tệp CSS và JS đã biên dịch thông qua Laravel Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Khu vực bọc toàn trang với hình nền đăng nhập -->
    <div class="login-page-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <div class="login-modal">
            <h4 class="welcome-title" style="text-align:center;">Sunburst Manager</h4>
            <p style="color: #64748b; font-size: 14px; padding: 8px; text-align: center;">Đây là trang diễn đàn cho thành viên chính thức của câu lạc bộ.</p>

            <!-- Biểu mẫu đăng nhập gọi hàm xử lý bất đồng bộ handleFormLogin từ app.js -->
            <form id="authLoginModalForm" onsubmit="handleFormLogin(event)">

                <!-- Khu vực hiển thị thông báo lỗi khi đăng nhập thất bại -->
                <div id="login-error-alert" style="display:none; background:#fef2f2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:20px; font-size:13px; text-align:center;"></div>

                <div class="form-group">
                    <label class="form-label" for="login-email">Your email:</label>
                    <input type="email" id="login-email" class="form-input" required placeholder="rennguyen@gmail.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="login-password">Your pass:</label>
                    <input type="password" id="login-password" class="form-input" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-save">Lez gooooo</button>

                <div style="text-align: center; margin-top: 15px;">
                    <p style="color: #64748b; font-size: 13px;">
                        Thành viên mới của Sunburst?
                        <a href="{{ route('register') }}" style="color: #3b82f6; text-decoration: none; font-weight: bold;">
                            Đăng ký tại đây.
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
