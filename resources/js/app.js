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

/**
 * Mở modal theo ID được truyền vào, thêm class hiển thị và bật kiểu flex style trực tiếp
 */
export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex'; // Ép hiển thị trực tiếp bằng JS
    }
}

/**
 * Đóng modal theo ID, gỡ class hiển thị, ẩn modal và reset form bên trong nếu tồn tại
 */
export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.style.display = 'none'; // Ẩn đi khi đóng
    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

// Đồng bộ gán hàm openModal và closeModal vào window để sử dụng toàn cục trên mọi giao diện
window.openModal = openModal;
window.closeModal = closeModal;
