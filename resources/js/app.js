/**
 * ==========================================================================
 * FILE: app.js (Entry Point)
 * Chức năng: Quản lý module chính, cấu hình các hàm global (gắn vào window)
 * để các file HTML/Blade có thể gọi trực tiếp, xử lý lọc dữ liệu,
 * điều khiển Modal và khởi tạo các thành phần giao diện khi trang tải xong.
 * ==========================================================================
 */

import * as AuthActions from './auth';
import * as MemberActions from './member';
import './calendar/calendar.js'; // Import module calendar chính từ thư mục con
import * as CalendarMemberActions from './calendar/addMember.js';
import { initInstrumentSelector, getSelectedInstruments } from './instrumentSelector';
import './animations/home';

// ==========================================================================
// 1. GÁN CÁC HÀM XỬ LÝ XÁC THỰC (AUTH) VÀO GLOBAL (WINDOW)
// ==========================================================================
window.handleFormLogin = AuthActions.handleFormLogin;
window.handleFormRegister = AuthActions.handleFormRegister;

// ==========================================================================
// 2. GÁN CÁC HÀM XỬ LÝ COMPONENT NHẠC CỤ (INSTRUMENT SELECTOR)
// ==========================================================================
window.initInstrumentSelector = initInstrumentSelector;
window.getSelectedInstruments = getSelectedInstruments;


// ==========================================================================
// 3. GÁN CÁC HÀM XỬ LÝ QUẢN LÝ THÀNH VIÊN & LỊCH TRÌNH
// ==========================================================================
window.generateActivationKey = MemberActions.generateActivationKey;
window.copyToClipboard = MemberActions.copyToClipboard;
window.prepareAndOpenEditModal = MemberActions.prepareAndOpenEditModal;
window.submitEditForm = MemberActions.submitEditForm;
window.deleteMember = MemberActions.deleteMember;
window.applyFilters = applyFilters;

// Gắn sự kiện kéo thả thành viên trong tạo lịch vào window nếu dùng trực tiếp trong HTML attribute
window.handleMemberDragStart = CalendarMemberActions.handleMemberDragStart;
window.handleMemberDrop = CalendarMemberActions.handleMemberDrop;
window.removeMemberChip = CalendarMemberActions.removeMemberChip;
window.filterAvailableMembers = CalendarMemberActions.filterAvailableMembers;
window.initInstrumentFilterOptions = CalendarMemberActions.initInstrumentFilterOptions;

// ==========================================================================
// 4. HÀM LỌC DỮ LIỆU THÀNH VIÊN (FILTER)
// ==========================================================================
export function applyFilters() {
    const role = document.getElementById('filter-role')?.value || '';
    const instrument = document.getElementById('filter-instrument')?.value.trim() || '';

    const url = new URL(window.location.origin + window.location.pathname);
    if (role) url.searchParams.set('role', role);
    if (instrument) url.searchParams.set('instrument', instrument);

    window.location.href = url.toString();
}

// ==========================================================================
// 5. QUẢN LÝ HỆ THỐNG MODAL (MỞ / ĐÓNG POPUP)
// ==========================================================================
export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active'); // Đồng bộ với .custom-modal.active trong CSS
    }
}

export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('active'); // Đồng bộ với CSS

    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

window.openModal = openModal;
window.closeModal = closeModal;

// Tự động gán sự kiện bấm nút X hoặc bấm ra ngoài modal để đóng
document.addEventListener('click', (e) => {
    // Xử lý khi bấm vào nút X có class close-btn
    const closeBtn = e.target.closest('.close-btn');
    if (closeBtn) {
        const modalId = closeBtn.getAttribute('data-modal');
        if (modalId) {
            closeModal(modalId);
        } else {
            const modal = closeBtn.closest('.custom-modal');
            if (modal) {
                modal.classList.remove('active');
            }
        }
    }

    // Xử lý khi bấm ra vùng nền xám bên ngoài modal
    if (e.target.classList.contains('custom-modal')) {
        e.target.classList.remove('active');
    }
});

// ==========================================================================
// 6. KHỞI TẠO TỰ ĐỘNG KHI DOM SẴN SÀNG
// ==========================================================================
document.addEventListener('DOMContentLoaded', () => {
    initInstrumentSelector();
    AuthActions.initRegisterSlider();
});
