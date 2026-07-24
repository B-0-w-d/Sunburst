import * as AuthActions from './auth';
import * as MemberActions from './member';
import './animations/home';

// Gán các hàm xử lý xác thực (Auth) vào đối tượng toàn cục window
window.handleFormLogin = AuthActions.handleFormLogin;
window.handleFormRegister = AuthActions.handleFormRegister;

// Gán các hàm xử lý thành viên (Member) vào đối tượng toàn cục window
window.generateActivationKey = MemberActions.generateActivationKey;
window.copyToClipboard = MemberActions.copyToClipboard;
window.prepareAndOpenEditModal = MemberActions.prepareAndOpenEditModal;
window.submitEditForm = MemberActions.submitEditForm;
window.deleteMember = MemberActions.deleteMember;

// Định nghĩa hàm applyFilters trực tiếp hoặc import từ module member nếu có
export function applyFilters() {
    const role = document.getElementById('filter-role')?.value || '';
    const instrument = document.getElementById('filter-instrument')?.value.trim() || '';

    const url = new URL(window.location.origin + window.location.pathname);
    if (role) url.searchParams.set('role', role);
    if (instrument) url.searchParams.set('instrument', instrument);

    window.location.href = url.toString();
}
window.applyFilters = applyFilters;

/**
 * Mở modal theo ID được truyền vào
 */
export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex';
    }
}

/**
 * Đóng modal theo ID
 */
export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.style.display = 'none';
    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

// Gán tường minh vào window để các sự kiện onclick trên HTML tìm thấy
window.openModal = openModal;
window.closeModal = closeModal;
