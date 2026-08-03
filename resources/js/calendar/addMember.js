// =========================================================================
// QUẢN LÝ KÉO THẢ THÀNH VIÊN TRONG FORM TẠO SỰ KIỆN
// =========================================================================

// Biến lưu trữ phần tử thành viên đang được kéo (drag)
let draggedMemberElement = null;

/**
 * Xử lý sự kiện khi bắt đầu kéo một thành viên (chip)
 */
export function handleMemberDragStart(e) {
    draggedMemberElement = e.currentTarget; // Lưu lại element đang được kéo
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.id); // Truyền đi ID của thành viên qua dataTransfer
}

/**
 * Xử lý sự kiện khi thả (drop) thành viên vào một vùng (khu vực) mới
 */
export function handleMemberDrop(e, targetZoneType) {
    e.preventDefault();
    if (!draggedMemberElement) return; // Nếu không có phần tử nào đang được kéo thì dừng

    // Xác định vùng đích đến (vùng đã chọn hoặc vùng khả dụng)
    const targetZone = targetZoneType === 'selected'
        ? document.getElementById('selectedMembersZone')
        : document.getElementById('availableMembersZone');

    if (!targetZone) return;

    // Nếu thả vào vùng 'selected' và thẻ chưa có nút xóa thì tạo thêm nút xóa (&times;)
    if (targetZoneType === 'selected' && !draggedMemberElement.querySelector('.btn-remove-chip')) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-remove-chip';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function() { removeMemberChip(this); };
        draggedMemberElement.appendChild(removeBtn);
    }
    // Ngược lại nếu thả về vùng 'available' thì gỡ bỏ nút xóa đi
    else if (targetZoneType === 'available') {
        const btn = draggedMemberElement.querySelector('.btn-remove-chip');
        if (btn) btn.remove();
    }

    // Di chuyển thẻ thành viên sang vùng đích
    targetZone.appendChild(draggedMemberElement);

    // Cập nhật lại giá trị cho thẻ input ẩn chứa danh sách ID thành viên
    updateMemberHiddenInput();

    // Reset lại biến kéo thả
    draggedMemberElement = null;
}

/**
 * Xử lý khi bấm nút xóa (dấu x) trực tiếp trên chip thành viên để trả về vùng ban đầu
 */
export function removeMemberChip(btn) {
    const chip = btn.closest('.member-chip'); // Tìm thẻ chứa nút xóa
    const availableZone = document.getElementById('availableMembersZone'); // Vùng danh sách ban đầu
    if (chip && availableZone) {
        btn.remove(); // Xóa nút x
        availableZone.appendChild(chip); // Đưa chip trở lại vùng ban đầu
        updateMemberHiddenInput(); // Cập nhật lại danh sách ID ẩn
    }
}

/**
 * Cập nhật chuỗi ID các thành viên đã được chọn vào ô input ẩn để gửi dữ liệu lên server
 */
export function updateMemberHiddenInput() {
    const selectedZone = document.getElementById('selectedMembersZone');
    if (!selectedZone) return;

    // Lấy tất cả các chip hiện có trong vùng đã chọn
    const chips = selectedZone.querySelectorAll('.member-chip');
    // Trích xuất lấy mảng các ID của thành viên
    const ids = Array.from(chips).map(chip => chip.dataset.id);

    // Gán chuỗi các ID cách nhau bằng dấu phẩy vào input ẩn
    const hiddenInput = document.getElementById('targetMemberIds');
    if (hiddenInput) {
        hiddenInput.value = ids.join(',');
    }
}
