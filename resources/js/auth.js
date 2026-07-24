/**
 * Xử lý đăng nhập thông qua AJAX (Fetch API)
 * @param {Event} event - Sự kiện submit từ form đăng nhập
 */
export function handleFormLogin(event) {
    event.preventDefault();

    const errorAlert = document.getElementById('login-error-alert');
    if (errorAlert) errorAlert.style.display = 'none';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/login', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            email: document.getElementById('login-email').value,
            password: document.getElementById('login-password').value
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // === LƯU ACCESS_TOKEN VÀO LOCALSTORAGE TẠI ĐÂY ===
            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
            }

            window.location.href = '/';
        } else {
            if (errorAlert) {
                errorAlert.textContent = data.message || 'Invalid credentials.';
                errorAlert.style.display = 'block';
            }
        }
    })
    .catch(() => {
        if (errorAlert) {
            errorAlert.textContent = 'Server connectivity fault.';
            errorAlert.style.display = 'block';
        }
    });
}

/**
 * Trả về mảng các nhạc cụ được tách từ chuỗi input phân tách bằng dấu phẩy
 * @param {string} id - ID của phần tử input nhạc cụ
 * @returns {string[]} Mảng các nhạc cụ đã được lọc sạch khoảng trắng
 */
export function getInstrumentArray(id) {
    const element = document.getElementById(id);
    if (!element) return [];
    return element.value ? element.value.split(',').map(i => i.trim()).filter(i => i !== '') : [];
}

/**
 * Xử lý đăng ký thành viên thông qua AJAX (Fetch API)
 * Đã sửa lỗi null và đồng bộ hóa logic nhạc cụ
 * @param {Event} event - Sự kiện submit từ form đăng ký
 */
export function handleFormRegister(event) {
    event.preventDefault();

    const errorAlert = document.getElementById('register-error-alert');
    if (errorAlert) errorAlert.style.display = 'none';

    // Lấy các element an toàn
    const nameInput = document.getElementById('reg-name');
    const emailInput = document.getElementById('reg-email');
    const birthdayInput = document.getElementById('reg-birthday');
    const passwordInput = document.getElementById('reg-password');
    const passwordConfirmInput = document.getElementById('reg-password-confirm');
    const keyInput = document.getElementById('reg-key');

    // Sử dụng getInstrumentArray để lấy mảng nhạc cụ giống như hàm xử lý phía member
    const instruments = getInstrumentArray('reg-instruments');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/register', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: nameInput ? nameInput.value : '',
            email: emailInput ? emailInput.value : '',
            birthday: birthdayInput ? birthdayInput.value || null : null,
            // Gửi mảng nhạc cụ đã được xử lý bởi getInstrumentArray
            instrument: instruments,
            password: passwordInput ? passwordInput.value : '',
            password_confirmation: passwordConfirmInput ? passwordConfirmInput.value : '',
            activation_key: keyInput ? keyInput.value : ''
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = '/';
        } else {
            if (errorAlert) {
                errorAlert.textContent = data.message || 'Registration failed.';
                errorAlert.style.display = 'block';
            }
        }
    })
    .catch(() => {
        if (errorAlert) {
            errorAlert.textContent = 'Server connectivity fault.';
            errorAlert.style.display = 'block';
        }
    });
}

/**
 * Đóng modal theo ID
 * @param {string} modalId - ID của modal cần đóng
 */
export function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}
