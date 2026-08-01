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
import './calendar'; // Import side-effect file calendar.js (đảm bảo nằm cùng thư mục hoặc đúng đường dẫn)
import { initInstrumentSelector, getSelectedInstruments } from './instrumentSelector';
import './animations/home';

// ==========================================================================
// 1. GÁN CÁC HÀM XỬ LÝ XÁC THỰC (AUTH) VÀO GLOBAL (WINDOW)
// Mục đích: Cho phép gọi trực tiếp onclick="..." từ các thẻ HTML form Login/Register.
// ==========================================================================
window.handleFormLogin = AuthActions.handleFormLogin;
window.handleFormRegister = AuthActions.handleFormRegister;

// ==========================================================================
// 2. GÁN CÁC HÀM XỬ LÝ COMPONENT NHẠC CỤ (INSTRUMENT SELECTOR)
// Mục đích: Hỗ trợ chọn, lọc và quản lý nhạc cụ của thành viên.
// ==========================================================================
window.initInstrumentSelector = initInstrumentSelector;
window.getSelectedInstruments = getSelectedInstruments;

// ==========================================================================
// 3. GÁN CÁC HÀM XỬ LÝ QUẢN LÝ THÀNH VIÊN (MEMBER ACTIONS)
// Mục đích: Phục vụ các thao tác kích hoạt tài khoản, sửa/xóa thành viên.
// ==========================================================================
window.generateActivationKey = MemberActions.generateActivationKey;
window.copyToClipboard = MemberActions.copyToClipboard;
window.prepareAndOpenEditModal = MemberActions.prepareAndOpenEditModal;
window.submitEditForm = MemberActions.submitEditForm;
window.deleteMember = MemberActions.deleteMember;
window.applyFilters = applyFilters; // Gắn hàm lọc vào window để gọi từ giao diện

// ==========================================================================
// 4. HÀM LỌC DỮ LIỆU THÀNH VIÊN (FILTER)
// Xử lý đọc giá trị từ ô chọn Vai trò (role) và Nhạc cụ (instrument),
// sau đó cập nhật lại URL query string để load lại trang theo bộ lọc.
// ==========================================================================
export function applyFilters() {
    const role = document.getElementById('filter-role')?.value || '';
    const instrument = document.getElementById('filter-instrument')?.value.trim() || '';

    const url = new URL(window.location.origin + window.location.pathname);
    if (role) url.searchParams.set('role', role);
    if (instrument) url.searchParams.set('instrument', instrument);

    window.location.href = url.toString(); // Chuyển hướng đến URL chứa bộ lọc mới
}

// ==========================================================================
// 5. QUẢN LÝ HỆ THỐNG MODAL (MỞ / ĐÓNG POPUP)
// ==========================================================================

// Mở modal dựa theo ID truyền vào
export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex'; // Hiển thị modal bằng Flexbox (căn giữa)
    }
}

// Đóng modal theo ID và tự động reset form bên trong (nếu tồn tại)
export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.style.display = 'none'; // Ẩn modal đi

    // Tìm form có ID dạng `${id}Form` bên trong modal để xóa sạch dữ liệu cũ khi đóng
    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

// Đưa hàm open/close modal ra global scope để gọi nhanh trong HTML
window.openModal = openModal;
window.closeModal = closeModal;

// ==========================================================================
// 6. KHỞI TẠO TỰ ĐỘNG KHI DOM SẴN SÀNG
// Lắng nghe sự kiện DOMContentLoaded để kích hoạt các script tương tác giao diện.
// ==========================================================================
document.addEventListener('DOMContentLoaded', () => {
    initInstrumentSelector(); // Khởi tạo trình chọn nhạc cụ
    AuthActions.initRegisterSlider(); // Kích hoạt trượt slider đăng ký và chuyển đổi chấm (dots)
});
