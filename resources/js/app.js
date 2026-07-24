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


// Xử lý closeModal chung
window.closeModal = function(modalId) {
    if (typeof MemberActions.closeModal === 'function') {
        MemberActions.closeModal(modalId);
    }
    if (typeof AuthActions.closeModal === 'function') {
        AuthActions.closeModal(modalId);
    }
};
