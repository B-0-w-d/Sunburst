/**
 * Gửi request bất đồng bộ tới server
 */
async function sendRequest(url, method, body = null) {
    const response = await fetch(url, {
        method,
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: body ? JSON.stringify(body) : null
    });
    return await response.json();
}

/**
 * Hiển thị modal dựa trên ID
 */
export function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('is-open');
}

/**
 * Đóng modal và reset form bên trong nếu có
 */
export function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('is-open');
    const form = document.getElementById(`${id}Form`);
    if (form) form.reset();
}

/**
 * Lấy danh sách nhạc cụ từ input string
 */
export function getInstrumentArray(id) {
    const element = document.getElementById(id);
    if (!element) return [];
    return element.value ? element.value.split(',').map(i => i.trim()).filter(i => i !== '') : [];
}

/**
 * Xử lý chung cho việc gửi form (Add/Edit)
 */
async function handleMemberSubmit(event, method, url, modalId) {
    event.preventDefault();
    const prefix = modalId === 'addMemberModal' ? 'add' : 'edit';
    const payload = {
        name: document.getElementById(`${prefix}-name`).value,
        email: document.getElementById(`${prefix}-email`).value,
        birthday: document.getElementById(`${prefix}-birthday`).value || null,
        role: document.getElementById(`${prefix}-role`).value,
        instrument: getInstrumentArray(`${prefix}-instruments`)
    };
    console.log('Payload gửi đi:', payload);

    const data = await sendRequest(url, method, payload);
    if (data.status === 'success') {
        window.location.reload();
    } else {
        alert(data.message || 'Operation failed.');
    }
}

/**
 * Chuẩn bị dữ liệu và mở modal Edit
 */
export function prepareAndOpenEditModal(id) {
    if (!id) return;
    const row = document.getElementById(`member-row-${id}`);
    if (!row) { alert("Error: Member data row could not be located."); return; }

    const hiddenIdInput = document.getElementById('edit-member-id');
    if (hiddenIdInput) hiddenIdInput.value = id;

    document.getElementById('edit-name').value = row.querySelector('[data-name]')?.textContent.trim() || '';
    document.getElementById('edit-email').value = row.querySelector('[data-email]')?.textContent.trim() || '';
    document.getElementById('edit-birthday').value = row.querySelector('[data-birthday-raw]')?.getAttribute('data-birthday-raw') || '';
    document.getElementById('edit-role').value = row.querySelector('[data-role]')?.getAttribute('data-role') || '';
    document.getElementById('edit-instruments').value = row.querySelector('[data-instruments-raw]')?.getAttribute('data-instruments-raw') || '';

    openModal('editMemberModal');
}

/**
 * Xử lý submit form cập nhật thành viên
 */
export async function submitEditForm(event) {
    const id = document.getElementById('edit-member-id')?.value;
    if (!id) { alert("Error: ID missing."); return; }
    await handleMemberSubmit(event, 'PUT', `/api/members/${id}`, 'editMemberModal');
}

/**
 * Xử lý xóa thành viên
 */
export async function deleteMember(id) {
    if (!id || !confirm('Are you absolutely sure?')) return;
    const data = await sendRequest(`/api/members/${id}`, 'DELETE');
    if (data.status === 'success') window.location.reload();
    else alert(data.message || 'Deletion failed.');
}

/**
 * Gửi yêu cầu tới server để tạo mã kích hoạt mới
 */
export async function generateActivationKey() {
    try {
        const response = await fetch('/api/members/generate-key', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        // Kiểm tra kết quả trả về từ Controller
        if (data.status === 'success') {
            // Cập nhật mã hiển thị lên giao diện
            document.getElementById('key-display').value = data.key;
            // Cập nhật thông tin thời hạn
            document.getElementById('key-expiry').textContent = `Expires at: ${new Date(data.expires_at).toLocaleString()}`;
        } else {
            alert(data.message || 'Failed to generate key.');
        }
    } catch (error) {
        console.error('Error generating key:', error);
        alert('An error occurred while generating the key.');
    }
}
export function copyToClipboard() {
    const keyInput = document.getElementById('key-display');
    if (!keyInput.value) return;

    keyInput.select();
    keyInput.setSelectionRange(0, 99999); // Dành cho mobile

    navigator.clipboard.writeText(keyInput.value).then(() => {
        alert("Key copied to clipboard!");
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
