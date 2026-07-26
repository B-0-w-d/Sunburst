<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | Sunburst Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

    </style>
</head>

<body>
    <div class="login-page-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <div class="login-modal">
            <h4 class="welcome-title" style="text-align:center;">Sunburst Manager</h4>
            <p style="color: #64748b; font-size: 14px; padding: 8px; text-align: center;">Đây là trang diễn đàn cho thành viên chính thức của câu lạc bộ.</p>

            <form id="authLoginModalForm" onsubmit="handleFormLogin(event)">

                <div id="login-error-alert" style="display:none; background:#fef2f2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:20px; font-size:13px; text-align:center;"></div>

                <!-- BƯỚC 1: Nhập Email -->
                <div class="form-group" id="step-email-group">
                    <label class="form-label" for="login-email">Your email:</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="email" id="login-email" class="form-input" required placeholder="rennguyen@gmail.com" style="flex: 1;">
                        <button type="button" id="btn-next-step" class="btn-save" style="width: auto; padding: 0 16px; margin-top: 0;" onclick="proceedToPassword()">Next</button>
                    </div>
                </div>

                <!-- BƯỚC 2: Nhập Password & Nút Login (Ban đầu bị ẩn đi có hiệu ứng) -->
                <div class="step-section hidden-step" id="step-password-group">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="form-label" for="login-password" style="margin-bottom: 0;">Your pass:</label>
                            <button type="button" onclick="backToEmail()" style="background: none; border: none; color: #3b82f6; font-size: 12px; cursor: pointer; padding: 0;">Quay lại</button>
                        </div>
                        <input type="password" id="login-password" class="form-input" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-save">Lez gooooo</button>
                </div>

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
