import { getSelectedInstruments } from './instrumentSelector';

/**
 * Xử lý đăng nhập AJAX
 */
export function handleFormLogin(event) {
    event.preventDefault();

    const errorAlert = document.getElementById('login-error-alert');
    if (errorAlert) errorAlert.style.display = 'none';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
        if (data.status === 'success' || data.success) {
            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
            }
            window.location.href = '/';
        } else {
            if (errorAlert) {
                errorAlert.textContent = data.message || 'Thông tin đăng nhập không chính xác.';
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

/**
 * Xử lý đăng ký AJAX
 */
export function handleFormRegister(event) {
    if (event) event.preventDefault();

    const errorAlert = document.getElementById('register-error-alert');
    if (errorAlert) errorAlert.style.display = 'none';

    const nameInput = document.getElementById('reg-name');
    const emailInput = document.getElementById('reg-email');
    const birthdayInput = document.getElementById('reg-birthday');
    const passwordInput = document.getElementById('reg-password');
    const passwordConfirmInput = document.getElementById('reg-password-confirm');
    const keyInput = document.getElementById('reg-key');

    if (passwordInput && passwordConfirmInput && passwordInput.value !== passwordConfirmInput.value) {
        if (errorAlert) {
            errorAlert.textContent = 'Mật khẩu xác nhận không khớp!';
            errorAlert.style.display = 'block';
        }
        return;
    }

    const instruments = getSelectedInstruments('register-instruments');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
            window.location.href = '/';
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

/**
 * Khởi tạo hiệu ứng trượt Slider & Chấm (Dots) cho form Đăng ký
 */
export function initRegisterSlider() {
    const track = document.getElementById('slider-track');
    if (!track) return; // Chỉ chạy khi ở trang đăng ký

    let currentStep = 1;
    const errorAlert = document.getElementById('register-error-alert');

    const dots = [
        document.getElementById('dot-1'),
        document.getElementById('dot-2'),
        document.getElementById('dot-3')
    ];

    function showError(msg) {
        if (errorAlert) {
            errorAlert.textContent = msg;
            errorAlert.style.display = 'block';
        }
    }

    function hideError() {
        if (errorAlert) {
            errorAlert.style.display = 'none';
        }
    }

    function goToStep(targetStep) {
        hideError();

        if (currentStep === 1 && targetStep === 2) {
            const name = document.getElementById('reg-name')?.value.trim();
            const email = document.getElementById('reg-email')?.value.trim();
            const birthday = document.getElementById('reg-birthday')?.value.trim();

            if (!name || !email || !birthday) {
                showError('Vui lòng điền đầy đủ Họ tên, Email và Ngày sinh!');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Địa chỉ Email không đúng định dạng!');
                return;
            }
        }

        currentStep = targetStep;

        const offset = (targetStep - 1) * -33.33333;
        track.style.transform = `translateX(${offset}%)`;

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

    document.getElementById('btn-step1-next')?.addEventListener('click', () => goToStep(2));
    document.getElementById('btn-step2-prev')?.addEventListener('click', () => goToStep(1));
    document.getElementById('btn-step2-next')?.addEventListener('click', () => goToStep(3));
    document.getElementById('btn-step3-prev')?.addEventListener('click', () => goToStep(2));
    document.getElementById('btn-submit-register')?.addEventListener('click', handleFormRegister);
}

// Tự động kích hoạt khi DOM tải xong
document.addEventListener('DOMContentLoaded', () => {
    initRegisterSlider();
});
