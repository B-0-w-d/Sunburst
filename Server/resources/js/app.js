// app.js
import * as AuthActions from './auth';
import * as MemberActions from './member';

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

// Xử lý closeModal chung
window.closeModal = function(modalId) {
    // Ưu tiên đóng bằng hàm của Member trước, nếu không thì dùng của Auth
    if (MemberActions.closeModal) {
        MemberActions.closeModal(modalId);
    } else if (AuthActions.closeModal) {
        AuthActions.closeModal(modalId);
    }
};
