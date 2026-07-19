<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Thẻ meta để file JS lấy được CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register | Dashboard</title>

    <!-- Vite load file CSS và JS đã biên dịch -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Giữ nguyên wrapper và background từ trang login -->
    <div class="login-page-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <div class="login-modal">
            <h4 class="welcome-title" style="text-align:center;">Create Account</h4>
            <p style="color: #64748b; font-size: 14px; padding: 8px; text-align: center;">Join Sunburst management site.</p>

            <!-- Form gọi hàm handleFormRegister -->
            <form id="authRegisterModalForm" onsubmit="handleFormRegister(event)">

                <div id="register-error-alert" style="display:none; background:#fef2f2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:20px; font-size:13px; text-align:center;"></div>

                <div class="form-group">
                    <label class="form-label" for="reg-name">Full Name:</label>
                    <input type="text" id="reg-name" class="form-input" required placeholder="Ren Nguyễn">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-email">Email:</label>
                    <input type="email" id="reg-email" class="form-input" required placeholder="rennguyen@gmail.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-birthday">Date of Birth:</label>
                    <input type="date" id="reg-birthday" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-instruments">Instruments (Comma separated)</label>
                    <input type="text" id="reg-instruments" class="form-input" placeholder="Vai trò của bạn: Vocal, Bass,...">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-password">Password:</label>
                    <input type="password" id="reg-password" class="form-input" required placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-password-confirm">Confirm Password:</label>
                    <input type="password" id="reg-password-confirm" class="form-input" required placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-key">Activation Key:</label>
                    <input type="text" id="reg-key" class="form-input" required placeholder="Liên hệ Ban chủ nhiệm để lấy mã">
                </div>

                <button type="submit" class="btn-save">Register</button>
            </form>

            <!-- Nút điều hướng ngược lại trang Login -->
            <div style="text-align: center; margin-top: 15px;">
                <p style="color: #64748b; font-size: 13px;">
                    Đã là thành viên của Sunburst?
                    <a href="{{ route('login') }}" style="color: #3b82f6; text-decoration: none; font-weight: bold;">
                        Đăng nhập đi má
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>
