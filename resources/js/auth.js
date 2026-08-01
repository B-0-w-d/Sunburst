/**
 * ==========================================================================
 * FILE: auth.js
 * Chức năng: Xử lý toàn bộ logic liên quan đến Xác thực (Đăng nhập, Đăng ký AJAX)
 * và điều khiển hiệu ứng trượt qua các bước (Multi-step Slider) của form Đăng ký.
 * ==========================================================================
 */

import { getSelectedInstruments } from './instrumentSelector';

// ==========================================================================
// 1. XỬ LÝ ĐĂNG NHẬP (LOGIN AJAX)
// ==========================================================================
export function handleFormLogin(event) {
    event.preventDefault(); // Ngăn trình duyệt reload trang mặc định khi submit form

    // Ẩn thông báo lỗi cũ nếu có
    const errorAlert = document.getElementById('login-error-alert');
    if (errorAlert) errorAlert.style.display = 'none';

    // Lấy mã bảo mật CSRF từ thẻ meta trong trang HTML (phục vụ bảo mật Laravel)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Gửi yêu cầu đăng nhập bằng phương thức Fetch API (AJAX)
    fetch('/login', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            email: document.getElementById('login-email')?.value,
            password: document.getElementById('login-password')?.value
        })
    })
    .then(res => res.json())
    .then(data => {
        // Kiểm tra nếu đăng nhập thành công
        if (data.status === 'success' || data.success) {
            // Lưu token vào localStorage nếu server trả về access_token
            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
            }
            window.location.href = '/'; // Chuyển hướng về trang chủ
        } else {
            // Hiển thị thông báo lỗi nếu tài khoản/mật khẩu sai
            if (errorAlert) {
                errorAlert.textContent = data.message || 'Thông tin đăng nhập không chính xác.';
                errorAlert.style.display = 'block';
            }
        }
    })
    .catch(() => {
        // Xử lý lỗi kết nối mạng hoặc lỗi server bất ngờ
        if (errorAlert) {
            errorAlert.textContent = 'Lỗi kết nối máy chủ!';
            errorAlert.style.display = 'block';
        }
    });
}

// ==========================================================================
// 2. XỬ LÝ ĐĂNG KÝ (REGISTER AJAX)
// ==========================================================================
export function handleFormRegister(event) {
    if (event) event.preventDefault();

    const errorAlert = document.getElementById('register-error-alert');
    if (errorAlert) errorAlert.style.display = 'none';

    // Lấy các phần tử input thông tin đăng ký
    const nameInput = document.getElementById('reg-name');
    const emailInput = document.getElementById('reg-email');
    const birthdayInput = document.getElementById('reg-birthday');
    const passwordInput = document.getElementById('reg-password');
    const passwordConfirmInput = document.getElementById('reg-password-confirm');
    const keyInput = document.getElementById('reg-key');

    // Kiểm tra tính khớp nhau của mật khẩu xác nhận
    if (passwordInput && passwordConfirmInput && passwordInput.value !== passwordConfirmInput.value) {
        if (errorAlert) {
            errorAlert.textContent = 'Mật khẩu xác nhận không khớp!';
            errorAlert.style.display = 'block';
        }
        return;
    }

    // Lấy danh sách nhạc cụ mà người dùng đã chọn qua component chọn nhạc cụ
    const instruments = getSelectedInstruments('register-instruments');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Gửi yêu cầu đăng ký lên server
    fetch('/register', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: nameInput ? nameInput.value.trim() : '',
            email: emailInput ? emailInput.value.trim() : '',
            birthday: birthdayInput ? birthdayInput.value || null : null,
            instrument: instruments,
            password: passwordInput ? passwordInput.value : '',
            password_confirmation: passwordConfirmInput ? passwordConfirmInput.value : '',
            activation_key: keyInput ? keyInput.value.trim() : ''
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' || data.success) {
            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
            }
            window.location.href = '/'; // Đăng ký thành công chuyển về trang chủ
        } else {
            if (errorAlert) {
                errorAlert.textContent = data.message || 'Đăng ký thất bại!';
                errorAlert.style.display = 'block';
            }
        }
    })
    .catch(() => {
        if (errorAlert) {
            errorAlert.textContent = 'Lỗi kết nối máy chủ!';
            errorAlert.style.display = 'block';
        }
    });
}

// ==========================================================================
// 3. KHỞI TẠO HIỆU ỨNG TRƯỢT SLIDER & CHẤM (DOTS) CHO FORM ĐĂNG KÝ MULTI-STEP
// ==========================================================================
export function initRegisterSlider() {
    const track = document.getElementById('slider-track');
    if (!track) return; // Chỉ chạy code này nếu đang ở trang có form đăng ký

    let currentStep = 1; // Theo dõi bước hiện tại (Mặc định bước 1)
    const errorAlert = document.getElementById('register-error-alert');

    // Danh sách các chấm chỉ báo bước (dots)
    const dots = [
        document.getElementById('dot-1'),
        document.getElementById('dot-2'),
        document.getElementById('dot-3')
    ];

    // Hàm hiển thị lỗi nội bộ trong slider
    function showError(msg) {
        if (errorAlert) {
            errorAlert.textContent = msg;
            errorAlert.style.display = 'block';
        }
    }

    // Hàm ẩn thông báo lỗi
    function hideError() {
        if (errorAlert) {
            errorAlert.style.display = 'none';
        }
    }

    // Hàm điều hướng chuyển bước (Step)
    function goToStep(targetStep) {
        hideError();

        // Kiểm tra điều kiện ràng buộc dữ liệu khi chuyển từ Bước 1 sang Bước 2
        if (currentStep === 1 && targetStep === 2) {
            const name = document.getElementById('reg-name')?.value.trim();
            const email = document.getElementById('reg-email')?.value.trim();
            const birthday = document.getElementById('reg-birthday')?.value.trim();

            if (!name || !email || !birthday) {
                showError('Vui lòng điền đầy đủ Họ tên, Email và Ngày sinh!');
                return;
            }

            // Kiểm tra định dạng email hợp lệ bằng biểu thức chính quy (Regex)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Địa chỉ Email không đúng định dạng!');
                return;
            }
        }

        currentStep = targetStep;

        // Tính toán khoảng cách dịch chuyển ngang của thanh slider (Mỗi bước chiếm 33.33%)
        const offset = (targetStep - 1) * -33.33333;
        track.style.transform = `translateX(${offset}%)`;

        // Cập nhật trạng thái active cho các chấm (dots) tương ứng
        dots.forEach((dot, index) => {
            if (dot) {
                if (index + 1 === currentStep) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            }
        });
    }

    // Gắn sự kiện chuyển bước cho các nút bấm trong form đăng ký
    document.getElementById('btn-step1-next')?.addEventListener('click', () => goToStep(2));
    document.getElementById('btn-step2-prev')?.addEventListener('click', () => goToStep(1));
    document.getElementById('btn-step2-next')?.addEventListener('click', () => goToStep(3));
    document.getElementById('btn-step3-prev')?.addEventListener('click', () => goToStep(2));
    document.getElementById('btn-submit-register')?.addEventListener('click', handleFormRegister);
}

// Tự động kích hoạt hiệu ứng slider khi trang tải xong DOM
document.addEventListener('DOMContentLoaded', () => {
    initRegisterSlider();
});
