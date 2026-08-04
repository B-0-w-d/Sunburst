// =========================================================================
// QUẢN LÝ KÉO THẢ VÀ LỌC THÀNH VIÊN TRONG FORM TẠO SỰ KIỆN
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
    // Ngược lại nếu thả về vùng 'available' thì gỡ bỏ nút xóa đi và kiểm tra lại bộ lọc
    else if (targetZoneType === 'available') {
        const btn = draggedMemberElement.querySelector('.btn-remove-chip');
        if (btn) btn.remove();

        // Kiểm tra xem có đang bật lọc nhạc cụ nào không để ẩn/hiện cho đúng
        const filterSelect = document.getElementById('filterInstrumentSelector');
        if (filterSelect && filterSelect.value) {
            filterAvailableMembers(filterSelect.value);
        } else {
            draggedMemberElement.style.display = '';
        }
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
    if (!chip || !availableZone) return;

    btn.remove(); // Xóa nút x
    availableZone.appendChild(chip); // Đưa chip trở lại vùng ban đầu

    // Kiểm tra lại bộ lọc nhạc cụ khi chip quay về vùng available
    const filterSelect = document.getElementById('filterInstrumentSelector');
    if (filterSelect && filterSelect.value) {
        filterAvailableMembers(filterSelect.value);
    } else {
        chip.style.display = '';
    }

    updateMemberHiddenInput(); // Cập nhật lại danh sách ID ẩn
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

/**
 * Lọc hiển thị các thẻ chip thành viên ở vùng 'available' dựa trên nhạc cụ
 */
export function filterAvailableMembers(selectedInstrument) {
    const availableZone = document.getElementById('availableMembersZone');
    if (!availableZone) return;

    const chips = availableZone.querySelectorAll('.member-chip');

    chips.forEach(chip => {
        // Hỗ trợ lấy dữ liệu nhạc cụ linh hoạt từ attribute data-instruments hoặc data-instrument
        const instrumentsRaw = chip.getAttribute('data-instruments') || chip.getAttribute('data-instrument') || '';
        let instruments = [];

        try {
            // Thử parse nếu backend render ra dạng JSON array (ví dụ: '["Guitar", "Piano"]')
            instruments = JSON.parse(instrumentsRaw);
        } catch (e) {
            // Nếu không phải JSON, cắt chuỗi theo dấu phẩy như thông thường
            instruments = instrumentsRaw.split(',').map(i => i.trim());
        }

        // Chuẩn hóa về chữ thường để so sánh không phân biệt hoa/thường
        const normalizedInstruments = instruments.map(i => String(i).trim().toLowerCase());
        const query = String(selectedInstrument || '').trim().toLowerCase();

        // Nếu không chọn gì hoặc chọn rỗng -> hiện tất cả
        if (!query) {
            chip.style.display = '';
        } else {
            // Kiểm tra xem nhạc cụ của thành viên có chứa từ khóa lọc hay không
            const match = normalizedInstruments.some(inst => inst.includes(query));
            chip.style.display = match ? '' : 'none';
        }
    });
}

/**
 * Tự động quét tất cả các thẻ chip thành viên để lấy danh sách nhạc cụ độc lập và điền vào ô select lọc
 */
export function initInstrumentFilterOptions(selectElementId = 'filterInstrumentSelector') {
    const selectElement = document.getElementById(selectElementId);
    if (!selectElement) return;

    // Lưu lại giá trị đang chọn hiện tại (nếu có)
    const currentValue = selectElement.value;

    // Dùng Set để lọc các nhạc cụ không bị trùng lặp
    const uniqueInstruments = new Set();

    // Quét toàn bộ chip thành viên ở cả vùng available và selected
    const allChips = document.querySelectorAll('.member-chip');

    allChips.forEach(chip => {
        const instrumentsRaw = chip.getAttribute('data-instruments') || chip.getAttribute('data-instrument') || '';
        let instruments = [];

        try {
            instruments = JSON.parse(instrumentsRaw);
        } catch (e) {
            instruments = instrumentsRaw.split(',').map(i => i.trim());
        }

        // Thêm từng nhạc cụ vào Set (chuẩn hóa chữ hoa/thường hoặc giữ nguyên tùy ý)
        instruments.forEach(inst => {
            const cleaned = String(inst).trim();
            if (cleaned) {
                uniqueInstruments.add(cleaned);
            }
        });
    });

    // Giữ lại option mặc định đầu tiên (ví dụ: Tất cả nhạc cụ) và xóa các option cũ
    const defaultOptionText = selectElement.options[0] ? selectElement.options[0].text : '-- Tất cả nhạc cụ --';
    selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;

    // Sắp xếp danh sách nhạc cụ theo bảng chữ cái A-Z và thêm vào select
    Array.from(uniqueInstruments).sort().forEach(inst => {
        const option = document.createElement('option');
        option.value = inst;
        option.textContent = inst;

        // Nếu trước đó option này đang được chọn thì giữ nguyên trạng thái selected
        if (inst.toLowerCase() === currentValue.toLowerCase()) {
            option.selected = true;
        }

        selectElement.appendChild(option);
    });
}
