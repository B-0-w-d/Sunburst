<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register | Sunburst Dashboard</title>

    <!-- Nạp CSS và JS qua Laravel Vite (đã gộp các class step vào app.css) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="login-page-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <div class="login-modal">
            <h4 class="welcome-title" style="text-align:center;">Create Account</h4>
            <p style="color: #64748b; font-size: 14px; padding: 4px 8px 12px; text-align: center;">Join Sunburst management site.</p>

            <!-- Thanh tiến trình nhỏ theo dõi các bước -->
            <div class="step-indicator">
                <div class="step-dot active" id="dot-1"></div>
                <div class="step-dot" id="dot-2"></div>
                <div class="step-dot" id="dot-3"></div>
            </div>

            <form id="authRegisterModalForm" onsubmit="handleFormRegister(event)">

                <div id="register-error-alert" style="display:none; background:#fef2f2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:20px; font-size:13px; text-align:center;"></div>

                <!-- BƯỚC 1: Thông tin cơ bản (Name, Email, Birthday) -->
                <div class="step-section" id="step-1-group">
                    <div class="form-group">
                        <label class="form-label" for="reg-name">Full Name:</label>
                        <input type="text" id="reg-name" class="form-input" placeholder="Ren Nguyễn">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-email">Email:</label>
                        <input type="email" id="reg-email" class="form-input" placeholder="rennguyen@gmail.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-birthday">Date of Birth:</label>
                        <input type="date" id="reg-birthday" class="form-input">
                    </div>

                    <button type="button" class="btn-save" onclick="nextStep(1)">Tiếp theo</button>
                </div>

                <!-- BƯỚC 2: Nhạc cụ & Mật khẩu (Instruments, Password, Confirm Password) -->
                <div class="step-section hidden-step" id="step-2-group">
                    <div class="form-group">
                        <label class="form-label" for="reg-instruments">Instruments (Comma separated):</label>
                        <input type="text" id="reg-instruments" class="form-input" placeholder="Vocal, Bass,...">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-password">Password:</label>
                        <input type="password" id="reg-password" class="form-input" placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-password-confirm">Confirm Password:</label>
                        <input type="password" id="reg-password-confirm" class="form-input" placeholder="••••••••">
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn-save" style="background: #e2e8f0; color: #475569;" onclick="prevStep(2)">Quay lại</button>
                        <button type="button" class="btn-save" onclick="nextStep(2)">Tiếp theo</button>
                    </div>
                </div>

                <!-- BƯỚC 3: Mã kích hoạt & Submit (Activation Key) -->
                <div class="step-section hidden-step" id="step-3-group">
                    <div class="form-group">
                        <label class="form-label" for="reg-key">Activation Key:</label>
                        <input type="text" id="reg-key" class="form-input" placeholder="Liên hệ Ban chủ nhiệm để lấy mã">
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn-save" style="background: #e2e8f0; color: #475569;" onclick="prevStep(3)">Quay lại</button>
                        <button type="submit" class="btn-save">Register</button>
                    </div>
                </div>
            </form>

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

    <script>
        function nextStep(currentStep) {
            if (currentStep === 1) {
                const name = document.getElementById('reg-name');
                const email = document.getElementById('reg-email');
                const birthday = document.getElementById('reg-birthday');

                if (!name.value || !email.value || !birthday.value) {
                    alert('Vui lòng điền đầy đủ thông tin ở bước này!');
                    return;
                }
                if (!email.checkValidity()) {
                    email.reportValidity();
                    return;
                }
            } else if (currentStep === 2) {
                const pass = document.getElementById('reg-password');
                const confirmPass = document.getElementById('reg-password-confirm');

                if (!pass.value || !confirmPass.value) {
                    alert('Vui lòng nhập mật khẩu đầy đủ!');
                    return;
                }
                if (pass.value !== confirmPass.value) {
                    alert('Mật khẩu xác nhận không khớp!');
                    return;
                }
            }

            document.getElementById(`step-${currentStep}-group`).classList.add('hidden-step');
            document.getElementById(`dot-${currentStep}`).classList.remove('active');

            const next = currentStep + 1;
            document.getElementById(`step-${next}-group`).classList.remove('hidden-step');
            document.getElementById(`dot-${next}`).classList.add('active');
        }

        function prevStep(currentStep) {
            document.getElementById(`step-${currentStep}-group`).classList.add('hidden-step');
            document.getElementById(`dot-${currentStep}`).classList.remove('active');

            const prev = currentStep - 1;
            document.getElementById(`step-${prev}-group`).classList.remove('hidden-step');
            document.getElementById(`dot-${prev}`).classList.add('active');
        }
    </script>
</body>

</html>
