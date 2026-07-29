import * as AuthActions from './auth';
import * as MemberActions from './member';
import './calendar'; // Đảm bảo file calendar.js nằm cùng thư mục hoặc đúng đường dẫn
import { initInstrumentSelector, getSelectedInstruments } from './instrumentSelector';
import './animations/home';

// 1. Gán các hàm xử lý xác thực (Auth) vào window
window.handleFormLogin = AuthActions.handleFormLogin;
window.handleFormRegister = AuthActions.handleFormRegister;

// 2. Gán các hàm xử lý Component Nhạc cụ vào window
window.initInstrumentSelector = initInstrumentSelector;
window.getSelectedInstruments = getSelectedInstruments;

// 3. Gán các hàm xử lý thành viên (Member) vào window
window.generateActivationKey = MemberActions.generateActivationKey;
window.copyToClipboard = MemberActions.copyToClipboard;
window.prepareAndOpenEditModal = MemberActions.prepareAndOpenEditModal;
window.submitEditForm = MemberActions.submitEditForm;
window.deleteMember = MemberActions.deleteMember;
window.applyFilters = applyFilters;

// 4. Lọc dữ liệu
export function applyFilters() {
    const role = document.getElementById('filter-role')?.value || '';
    const instrument = document.getElementById('filter-instrument')?.value.trim() || '';

    const url = new URL(window.location.origin + window.location.pathname);
    if (role) url.searchParams.set('role', role);
    if (instrument) url.searchParams.set('instrument', instrument);

    window.location.href = url.toString();
}

// 5. Quản lý Modal
export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex';
    }
}

export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.style.display = 'none';
    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

window.openModal = openModal;
window.closeModal = closeModal;

// 6. Tự động khởi tạo Component Nhạc cụ và Slider Đăng ký khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    initInstrumentSelector();
    AuthActions.initRegisterSlider(); // <-- Kích hoạt trượt slider và chuyển đổi chấm (dots)
});
