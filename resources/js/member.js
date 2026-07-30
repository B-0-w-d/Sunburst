/**
 * Gửi request bất đồng bộ tới server thông qua Fetch API
 * @param {string} url - Đường dẫn endpoint cần gọi
 * @param {string} method - HTTP method (GET, POST, PUT, DELETE,...)
 * @param {Object|null} body - Dữ liệu gửi đi (JSON object)
 * @returns {Promise<Object>} Dữ liệu JSON trả về từ server
 */
async function sendRequest(url, method, body = null) {
    const token = localStorage.getItem('access_token');

    const response = await fetch(url, {
        method,
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            ...(token ? { 'Authorization': `Bearer ${token}` } : {})
        },
        body: body ? JSON.stringify(body) : null
    });
    return await response.json();
}

/**
 * Hiển thị modal dựa trên ID bằng cách thêm class 'is-open'
 * @param {string} id - ID của phần tử modal
 */
export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('is-open');
}

/**
 * Đóng modal theo ID, gỡ class 'is-open' và reset form bên trong nếu tồn tại
 * @param {string} id - ID của phần tử modal
 */
export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

/**
 * Lấy danh sách nhạc cụ/giá trị từ input dạng chuỗi phân tách bằng dấu phẩy
 * @param {string} id - ID của input chứa chuỗi giá trị
 * @returns {string[]} Mảng các giá trị đã được làm sạch khoảng trắng
 */
export function getInstrumentArray(id) {
    const element = document.getElementById(id);
    if (!element) return [];
    return element.value ? element.value.split(',').map(i => i.trim()).filter(i => i !== '') : [];
}

/**
 * Xử lý chung cho việc gửi form (Thêm/Sửa thông tin thành viên)
 * @param {Event} event - Sự kiện submit form
 * @param {string} method - HTTP method (POST/PUT)
 * @param {string} url - Endpoint nhận dữ liệu
 * @param {string} modalId - ID của modal tương ứng
 */
async function handleMemberSubmit(event, method, url, modalId) {
    event.preventDefault();
    const prefix = modalId === 'addMemberModal' ? 'add' : 'edit';

    // Tạo đối tượng payload chứa thông tin thành viên từ form
    const payload = {
        name: document.getElementById(`${prefix}-name`)?.value || '',
        email: document.getElementById(`${prefix}-email`)?.value || '',
        birthday: document.getElementById(`${prefix}-birthday`)?.value || null,
        instrument: getInstrumentArray(`${prefix}-instruments`)
    };

    // Kiểm tra và đính kèm danh sách thành viên tham gia (nếu có dùng component kéo thả member-selector)
    const targetMemberIdsInput = document.getElementById('targetMemberIds');
    if (targetMemberIdsInput) {
        payload.target_member_ids = targetMemberIdsInput.value
            ? targetMemberIdsInput.value.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id))
            : [];
    }

    // Kiểm tra và đính kèm role nếu trường select tồn tại trong DOM
    const roleSelect = document.getElementById(`${prefix}-role`);
    if (roleSelect) {
        payload.role = roleSelect.value;
    }

    const data = await sendRequest(url, method, payload);
    if (data.status === 'success') {
        window.location.reload();
    } else {
        alert(data.message || 'Operation failed.');
    }
}

/**
 * Chuẩn bị dữ liệu từ bảng giao diện và mở modal Edit tương ứng
 * @param {string} id - ID của thành viên cần chỉnh sửa
 */
export function prepareAndOpenEditModal(id) {
    if (!id) return;
    const row = document.getElementById(`member-row-${id}`);
    if (!row) { alert("Error: Member data row could not be located."); return; }

    // 1. Gán giá trị cho các trường bắt buộc tồn tại trên form chỉnh sửa
    document.getElementById('edit-member-id').value = id;
    document.getElementById('edit-name').value = row.querySelector('[data-name]')?.textContent.trim() || '';
    document.getElementById('edit-email').value = row.querySelector('[data-email]')?.textContent.trim() || '';
    document.getElementById('edit-birthday').value = row.querySelector('[data-birthday-raw]')?.getAttribute('data-birthday-raw') || '';
    document.getElementById('edit-instruments').value = row.querySelector('[data-instruments-raw]')?.getAttribute('data-instruments-raw') || '';

    // 2. Gán giá trị role an toàn nếu phần tử select tồn tại trong DOM (dành cho cấp quản lý)
    const roleSelect = document.getElementById('edit-role');
    if (roleSelect) {
        roleSelect.value = row.querySelector('[data-role]')?.getAttribute('data-role') || 'member';
    }

    // 3. Mở modal chỉnh sửa
    openModal('editMemberModal');
}

/**
 * Xử lý submit form cập nhật thông tin thành viên
 * @param {Event} event - Sự kiện submit form chỉnh sửa
 */
export async function submitEditForm(event) {
    const id = document.getElementById('edit-member-id')?.value;
    if (!id) { alert("Error: ID missing."); return; }
    await handleMemberSubmit(event, 'PUT', `/api/members/${id}`, 'editMemberModal');
}

/**
 * Xử lý xóa thành viên khỏi hệ thống sau khi người dùng xác nhận
 * @param {string} id - ID của thành viên cần xóa
 */
export async function deleteMember(id) {
    if (!id || !confirm('Are you absolutely sure?')) return;
    const data = await sendRequest(`/api/members/${id}`, 'DELETE');
    if (data.status === 'success') window.location.reload();
    else alert(data.message || 'Deletion failed.');
}

/**
 * Gửi yêu cầu tới server để tạo mã kích hoạt tài khoản mới
 */
export async function generateActivationKey() {
    try {
        const token = localStorage.getItem('access_token');

        const response = await fetch('/api/members/generate-key', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                ...(token ? { 'Authorization': `Bearer ${token}` } : {})
            }
        });

        const data = await response.json();

        if (data.status === 'success') {
            document.getElementById('key-display').value = data.key;
            document.getElementById('key-expiry').textContent = `Expires at: ${new Date(data.expires_at).toLocaleString()}`;
        } else {
            alert(data.message || 'Failed to generate key.');
        }
    } catch (error) {
        console.error('Error generating key:', error);
        alert('An error occurred while generating the key.');
    }
}

/**
 * Sao chép mã kích hoạt hiện tại vào bộ nhớ tạm (clipboard) của thiết bị
 */
export function copyToClipboard() {
    const keyInput = document.getElementById('key-display');
    if (!keyInput.value) return;

    keyInput.select();
    keyInput.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(keyInput.value).then(() => {
        alert("Key copied to clipboard!");
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

/**
 * Thu thập giá trị bộ lọc và reload lại trang theo query params
 */
export function applyFilters() {
    const role = document.getElementById('filter-role')?.value || '';
    const instrument = document.getElementById('filter-instrument')?.value.trim() || '';

    const url = new URL(window.location.origin + window.location.pathname);

    if (role) {
        url.searchParams.set('role', role);
    }
    if (instrument) {
        url.searchParams.set('instrument', instrument);
    }

    window.location.href = url.toString();
}

/**
 * =========================================================================
 * XỬ LÝ KÉO THẢ CHỌN THÀNH VIÊN (MEMBER-SELECTOR COMPONENT LOGIC)
 * =========================================================================
 */
let draggedMemberElement = null;

window.handleMemberDragStart = function(e) {
    draggedMemberElement = e.currentTarget;
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.id);
};

window.handleMemberDrop = function(e, targetZoneType) {
    e.preventDefault();
    if (!draggedMemberElement) return;

    const targetZone = targetZoneType === 'selected'
        ? document.getElementById('selectedMembersZone')
        : document.getElementById('availableMembersZone');

    if (!targetZone) return;

    // Nếu thả vào vùng selected mà chưa có nút xóa thì tự động gắn thêm nút xóa
    if (targetZoneType === 'selected' && !draggedMemberElement.querySelector('.btn-remove-chip')) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-remove-chip';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function() { window.removeMemberChip(this); };
        draggedMemberElement.appendChild(removeBtn);
    }
    else if (targetZoneType === 'available') {
        // Nếu trả ngược về vùng available thì gỡ bỏ nút xóa
        const btn = draggedMemberElement.querySelector('.btn-remove-chip');
        if (btn) btn.remove();
    }

    targetZone.appendChild(draggedMemberElement);
    window.updateMemberHiddenInput();
    draggedMemberElement = null;
};

window.removeMemberChip = function(btn) {
    const chip = btn.closest('.member-chip');
    const availableZone = document.getElementById('availableMembersZone');
    if (!chip || !availableZone) return;

    btn.remove();
    availableZone.appendChild(chip);
    window.updateMemberHiddenInput();
};

window.updateMemberHiddenInput = function() {
    const selectedZone = document.getElementById('selectedMembersZone');
    if (!selectedZone) return;

    const chips = selectedZone.querySelectorAll('.member-chip');
    const ids = Array.from(chips).map(chip => chip.dataset.id);

    const hiddenInput = document.getElementById('targetMemberIds');
    if (hiddenInput) {
        hiddenInput.value = ids.join(',');
    }
};
