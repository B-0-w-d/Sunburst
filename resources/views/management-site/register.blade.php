<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Đăng ký | Sunburst Dashboard</title>

    <!-- Import CSS và JS chính qua Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="auth-page-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}');">
        <div class="auth-modal">

            <div class="text-modal-header">
            <h1 class="text-modal-title">Chào thành viên mới nhá!</h1>
                <p class="text-modal-subtitle">Nhớ hỏi Ban Chủ nhiệm key đăng ký, lát dùng đó</p>
            </div>

            <!-- Thanh tiến trình dạng Chấm (Dots) -->
            <x-progressDot />

            <!-- Div Báo lỗi -->
            <div id="register-error-alert"></div>

            <!-- Form Đăng ký -->
            <form id="registerForm" onsubmit="return false;">
                <div class="slider-window">
                    <div class="slider-track" id="slider-track">

                        <!-- BƯỚC 1 -->
                        <div class="step-pane" id="pane-1">
                            <div class="form-group">
                                <label class="text-form
                                    " for="reg-name">Tên hoặc Nickname:</label>
                                <input type="text" id="reg-name" class="form-input" placeholder="Ren Nguyễn">
                            </div>

                            <div class="form-group">
                                <label class="text-form
                                    " for="reg-email">Email:</label>
                                <input type="email" id="reg-email" class="form-input" placeholder="cc@cc.com">
                            </div>

                            <div class="form-group">
                                <label class="text-form
                                    " for="reg-birthday">Năm sinh: </label>
                                <input type="date" id="reg-birthday" class="form-input">
                            </div>

                            <div class="btn-group">
                                <button type="button" id="btn-step1-next" class="btn btn-primary">Tiếp theo →</button>
                            </div>
                        </div>

                        <!-- BƯỚC 2 -->
                        <div class="step-pane" id="pane-2">
                            <label class="text-form
                                " style="font-size: 14px; margin-bottom: 12px;">
                                Hãy chọn sở trường của bạn:
                            </label>

                            <!-- Sử dụng Blade Component nhạc cụ -->
                            <x-instrumentSelect id="register-instruments" />

                            <div class="btn-group">
                                <button type="button" id="btn-step2-prev" class="btn btn-secondary">← Quay lại</button>
                                <button type="button" id="btn-step2-next" class="btn btn-primary">Tiếp theo →</button>
                            </div>
                        </div>

                        <!-- BƯỚC 3 -->
                        <div class="step-pane" id="pane-3">
                            <div class="form-group">
                                <label class="text-form
                                    " for="reg-password">Mật khẩu *</label>
                                <input type="password" id="reg-password" class="form-input" placeholder="••••••••" autocomplete="new-password">
                            </div>

                            <div class="form-group">
                                <label class="text-form
                                    " for="reg-password-confirm">Xác nhận mật khẩu *</label>
                                <input type="password" id="reg-password-confirm" class="form-input" placeholder="••••••••" autocomplete="new-password">
                            </div>

                            <div class="form-group">
                                <label class="text-form
                                    " for="reg-key">Mã kích hoạt (Activation Key) *</label>
                                <input type="text" id="reg-key" class="form-input" placeholder="Nhập mã từ Ban chủ nhiệm">
                            </div>

                            <div class="btn-group">
                                <button type="button" id="btn-step3-prev" class="btn btn-secondary">← Quay lại</button>
                                <button type="button" id="btn-submit-register" class="btn btn-primary">Đăng ký ngay</button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            <div class="footer-link">
                Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập đi má</a>
            </div>

        </div>
    </div>
</body>

</html>
