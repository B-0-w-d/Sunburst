import * as AuthActions from './auth';
import * as MemberActions from './member';
import './animations/home';

// Gán Auth
window.handleFormLogin = AuthActions.handleFormLogin;
window.handleFormRegister = AuthActions.handleFormRegister;

// Gán Member
window.generateActivationKey = MemberActions.generateActivationKey;
window.copyToClipboard = MemberActions.copyToClipboard;
window.openModal = MemberActions.openModal;
window.prepareAndOpenEditModal = MemberActions.prepareAndOpenEditModal;
window.submitEditForm = MemberActions.submitEditForm;
window.deleteMember = MemberActions.deleteMember;


export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex'; // Ép hiển thị trực tiếp bằng JS
    }
}

export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.style.display = 'none'; // Ẩn đi khi đóng
    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

// Xử lý closeModal chung
window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('is-open');
    }
};
