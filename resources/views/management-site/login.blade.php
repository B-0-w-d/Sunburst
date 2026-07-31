<!DOCTYPE html>
{{-- Khai báo ngôn ngữ cho trang web dựa trên cấu hình locale của ứng dụng Laravel --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Token CSRF bảo mật cho các request phương thức POST/PUT/DELETE --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | Sunburst Dashboard</title>

    {{-- Tích hợp các file tài nguyên CSS và JS chính thông qua Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Khung bọc trang đăng nhập, cài đặt hình nền (background) full màn hình bằng ảnh login-background.jpg -->
    <div class="login-page-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <!-- Khung cửa sổ modal chứa nội dung form đăng nhập -->
        <div class="login-modal">

            <!-- Tiêu đề trang đăng nhập -->
            <div class="modal-header">
                <h4 class="modal-title" style="text-align: center; margin-bottom: 4px;">Sunburst Manager</h4>
                <p style="color: #64748b; font-size: 14px; padding: 4px 8px 0 8px; text-align: center;">Đây là trang diễn đàn cho thành viên chính thức của câu lạc bộ.</p>
            </div>

            <!-- Form đăng nhập, kích hoạt hàm handleFormLogin khi submit -->
            <form id="authLoginModalForm" onsubmit="handleFormLogin(event)">

                <!-- Khung thông báo lỗi khi đăng nhập thất bại (mặc định ẩn) -->
                <div id="login-error-alert" style="display:none; background:#fef2f2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:20px; font-size:13px; text-align:center;"></div>

                <!-- BƯỚC 1: Nhập Email tài khoản -->
                <div class="form-group step-section" id="step-email-group">
                    <label class="form-label" for="login-email">Your email:</label>
                    <div style="display: flex; gap: 8px;">
                        <!-- Ô nhập địa chỉ email -->
                        <input type="email" id="login-email" class="form-input" required placeholder="rennguyen@gmail.com" style="flex: 1;">
                        <!-- Nút bấm chuyển sang bước tiếp theo -->
                        <button type="button" id="btn-next-step" class="btn btn-primary" style="width: auto; padding: 0 20px; margin-top: 0;" onclick="proceedToPassword()">Next</button>
                    </div>
                </div>

                <!-- BƯỚC 2: Nhập Password & Nút Login (mặc định bị ẩn bằng class hidden-step) -->
                <div class="step-section hidden-step" id="step-password-group">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="form-label" for="login-password" style="margin-bottom: 0;">Your pass:</label>
                            <!-- Nút bấm quay lại bước nhập email -->
                            <button type="button" onclick="backToEmail()" style="background: none; border: none; color: #3b82f6; font-size: 12px; cursor: pointer; padding: 0;">Quay lại</button>
                        </div>
                        <!-- Ô nhập mật khẩu -->
                        <input type="password" id="login-password" class="form-input" placeholder="••••••••">
                    </div>

                    <!-- Nút xác nhận gửi thông tin đăng nhập -->
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Lez gooooo</button>
                </div>

                <!-- Khu vực điều hướng chuyển sang trang đăng ký thành viên mới -->
                <div style="text-align: center; margin-top: 20px;">
                    <p style="color: #64748b; font-size: 13px;">
                        Thành viên mới của Sunburst?
                        <a href="{{ route('register') }}" style="color: #dc2626; text-decoration: none; font-weight: bold;">
                            Đăng ký đi ba.
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hàm chuyển sang bước nhập mật khẩu khi bấm Next hoặc nhấn Enter ở ô email
        function proceedToPassword() {
            const emailInput = document.getElementById('login-email');

            // Kiểm tra xem email đã được nhập hợp lệ chưa
            if (!emailInput.value || !emailInput.checkValidity()) {
                emailInput.reportValidity();
                return;
            }

            // Ẩn nhóm email, hiện nhóm password với hiệu ứng mượt mà
            document.getElementById('step-email-group').classList.add('hidden-step');

            const passwordGroup = document.getElementById('step-password-group');
            passwordGroup.classList.remove('hidden-step');

            // Tự động focus vào ô nhập mật khẩu
            setTimeout(() => {
                document.getElementById('login-password').focus();
            }, 200);
        }

        // Cho phép nhấn phím Enter ở ô email để chuyển sang bước sau thay vì submit form ngay
        document.getElementById('login-email').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                proceedToPassword();
            }
        });

        // Hàm cho phép quay lại chỉnh sửa email nếu cần
        function backToEmail() {
            document.getElementById('step-password-group').classList.add('hidden-step');
            document.getElementById('step-email-group').classList.remove('hidden-step');
            document.getElementById('login-email').focus();
        }
    </script>
</body>

</html>
