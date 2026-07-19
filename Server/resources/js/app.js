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
    // Attempt to close via MemberActions first
    if (typeof MemberActions.closeModal === 'function') {
        MemberActions.closeModal(modalId);
    }

    // Also attempt via AuthActions in case the ID belongs to that module
    if (typeof AuthActions.closeModal === 'function') {
        AuthActions.closeModal(modalId);
    }
};
