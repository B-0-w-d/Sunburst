/**
 * Gửi request bất đồng bộ tới server thông qua Fetch API
 * @param {string} url - Đường dẫn endpoint cần gọi
 * @param {string} method - HTTP method (GET, POST, PUT, DELETE,...)
 * @param {Object|null} body - Dữ liệu gửi đi (JSON object)
 * @returns {Promise<Object>} Dữ liệu JSON trả về từ server
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
 * Lấy danh sách nhạc cụ từ input dạng chuỗi phân tách bằng dấu phẩy
 * @param {string} id - ID của input chứa chuỗi nhạc cụ
 * @returns {string[]} Mảng các nhạc cụ đã được làm sạch khoảng trắng
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
        name: document.getElementById(`${prefix}-name`).value,
        email: document.getElementById(`${prefix}-email`).value,
        birthday: document.getElementById(`${prefix}-birthday`).value || null,
        instrument: getInstrumentArray(`${prefix}-instruments`)
    };

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
            // Cập nhật mã kích hoạt vừa tạo hiển thị lên giao diện
            document.getElementById('key-display').value = data.key;
            // Cập nhật thông tin thời hạn hết hiệu lực của mã
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
    keyInput.setSelectionRange(0, 99999); // Hỗ trợ tương thích trên thiết bị di động

    navigator.clipboard.writeText(keyInput.value).then(() => {
        alert("Key copied to clipboard!");
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
