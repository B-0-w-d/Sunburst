// =========================================================================
// XỬ LÝ MODAL TẠO SỰ KIỆN MỚI & CHỈNH SỬA (CREATE / EDIT EVENT)
// =========================================================================

import { createEventApi, updateEventApi, deleteEventApi } from './eventApi.js';
import { removeMemberChip } from './addMember.js';

// Hàm xử lý ẩn/hiện cấu hình trong Modal Tạo Mới theo trạng thái (POLL vs CONFIRMED)
export function initCreateEventModalLogic() {
    const eventStatusSelect = document.getElementById('eventStatus');
    if (eventStatusSelect) {
        eventStatusSelect.addEventListener('change', function () {
            const isPoll = this.value === 'POLL';
            const pollSec = document.getElementById('pollConfigSection');
            const confSec = document.getElementById('confirmedConfigSection');
            if (pollSec) pollSec.style.display = isPoll ? 'block' : 'none';
            if (confSec) confSec.style.display = isPoll ? 'none' : 'block';
        });
    }

    const openCreateBtn = document.getElementById('openCreateModalBtn');
    if (openCreateBtn) {
        openCreateBtn.addEventListener('click', () => {
            // Đổi tiêu đề về chế độ Tạo mới
            const modalTitleElem = document.querySelector('#createEventModal .modal-content h3');
            if (modalTitleElem) modalTitleElem.textContent = 'Tạo Sự Kiện Hoặc Khảo Sát Mới';

            const form = document.getElementById('createEventForm');
            if (form) {
                form.reset();
                delete form.dataset.editingId;
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerText = 'Tạo sự kiện';
                    submitBtn.style.width = '100%'; // Trả lại full width khi tạo mới
                    submitBtn.style.flex = 'unset';
                }
            }

            // Xóa nút Xóa nếu đang hiển thị
            const existingDeleteBtn = document.getElementById('dynamicDeleteEventBtn');
            if (existingDeleteBtn) existingDeleteBtn.remove();

            if (typeof window.openModal === 'function') {
                window.openModal('createEventModal');
            }
        });
    }

    // Xử lý Submit Form Tạo mới / Cập nhật sự kiện từ modal này
    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm && !createEventForm.dataset.listenerAttached) {
        createEventForm.dataset.listenerAttached = "true";
        createEventForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const statusVal = document.getElementById('eventStatus').value;
            const targetMembersVal = document.getElementById('targetMemberIds')?.value || '';

            let payload = {
                title: document.getElementById('eventTitle').value,
                type: document.getElementById('eventType').value,
                status: statusVal,
                target_member_ids: targetMembersVal.split(',').map(s => s.trim()).filter(Boolean),
            };

            if (statusVal === 'POLL') {
                payload.poll_config = {
                    start_date: document.getElementById('pollStartDate').value,
                    end_date: document.getElementById('pollEndDate').value,
                    step_minutes: 30
                };
            } else {
                payload.start_time = document.getElementById('startTime').value;
                payload.end_time = document.getElementById('endTime').value;
            }

            const editingId = this.dataset.editingId;
            try {
                if (editingId) {
                    await updateEventApi(editingId, payload);
                    alert('Cập nhật thành công!');
                } else {
                    await createEventApi(payload);
                    alert('Tạo thành công!');
                }
                window.closeModal('createEventModal');
                createEventForm.reset();
                if (typeof window.loadAllEvents === 'function') {
                    window.loadAllEvents();
                }
            } catch (ex) {
                console.error(ex);
                alert(ex.message || 'Lỗi kết nối máy chủ.');
            }
        });
    }
}

// Hàm phục vụ binding dữ liệu khi click vào lịch tuần để sửa sự kiện qua form này
export function actionBindWeeklyEventClickForCreate(ev, setActiveEventIdCallback, loadEventsCallback) {
    // Đổi tiêu đề modal thành Chỉnh sửa khi click vào sự kiện trên lịch
    const modalTitleElem = document.querySelector('#createEventModal .modal-content h3');
    if (modalTitleElem) modalTitleElem.textContent = `Chỉnh Sửa: ${ev.title || 'Sự kiện'}`;

    const activeEventId = ev._id || ev.id || (ev._id && ev._id.$oid);
    if (setActiveEventIdCallback) setActiveEventIdCallback(activeEventId);

    const titleInput = document.getElementById('eventTitle');
    if (titleInput) titleInput.value = ev.title || '';

    const typeSelect = document.getElementById('eventType');
    if (typeSelect) typeSelect.value = ev.type || 'PRACTICE';

    const statusSelect = document.getElementById('eventStatus');
    if (statusSelect) {
        statusSelect.value = ev.status || 'CONFIRMED';
        statusSelect.dispatchEvent(new Event('change'));
    }

    const startTimeInput = document.getElementById('startTime');
    const endTimeInput = document.getElementById('endTime');

    if (ev.start_time && startTimeInput) {
        startTimeInput.value = ev.start_time.replace(' ', 'T').substring(0, 16);
    }
    if (ev.end_time && endTimeInput) {
        endTimeInput.value = ev.end_time.replace(' ', 'T').substring(0, 16);
    }

    // Xử lý thành viên
    const availableZone = document.getElementById('availableMembersZone');
    const selectedZone = document.getElementById('selectedMembersZone');
    const hiddenTargetInput = document.getElementById('targetMemberIds');

    if (selectedZone && availableZone) {
        selectedZone.querySelectorAll('.member-chip').forEach(chip => {
            const removeBtn = chip.querySelector('.btn-remove-chip');
            if (removeBtn) removeBtn.remove();
            availableZone.appendChild(chip);
        });

        const targetIds = ev.target_member_ids || [];
        if (hiddenTargetInput) hiddenTargetInput.value = targetIds.join(',');

        targetIds.forEach(memberId => {
            const chip = availableZone.querySelector(`.member-chip[data-id="${memberId}"]`);
            if (chip) {
                if (!chip.querySelector('.btn-remove-chip')) {
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn-remove-chip';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.onclick = function() { removeMemberChip(this); };
                    chip.appendChild(removeBtn);
                }
                selectedZone.appendChild(chip);
            }
        });
    }

    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm) createEventForm.dataset.editingId = activeEventId;

    const submitBtn = createEventForm?.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.innerText = 'Cập nhật sự kiện';
    }

    // TỰ ĐỘNG CHÈN NÚT XÓA VÀO DƯỚI CÙNG KHI Ở CHẾ ĐỘ SỬA
        if (submitBtn && !document.getElementById('dynamicDeleteEventBtn')) {
            const parentContainer = submitBtn.parentElement;
            if (parentContainer) {
                // Ép thẻ cha thành khối riêng biệt nằm dưới cùng, hiển thị dạng hàng ngang chuẩn
                parentContainer.style.display = 'flex';
                parentContainer.style.flexDirection = 'row';
                parentContainer.style.justifyContent = 'space-between';
                parentContainer.style.gap = '12px';
                parentContainer.style.width = '100%';
                parentContainer.style.marginTop = '20px';

                // Reset style nút Cập nhật thành dạng nút ngang bình thường
                submitBtn.style.flex = '1';
                submitBtn.style.width = 'auto';
                submitBtn.style.height = '45px';
                submitBtn.style.writingMode = 'horizontal-tb';

                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.id = 'dynamicDeleteEventBtn';
                deleteBtn.className = submitBtn.className;
                deleteBtn.style.backgroundColor = '#dc3545'; // Màu đỏ xóa
                deleteBtn.style.color = '#fff';
                deleteBtn.style.flex = '1';
                deleteBtn.style.width = 'auto';
                deleteBtn.style.height = '45px';
                deleteBtn.style.writingMode = 'horizontal-tb';
                deleteBtn.innerText = 'Xóa sự kiện';

                deleteBtn.addEventListener('click', async function () {
                    if (!activeEventId) {
                        alert('Không tìm thấy ID sự kiện để xóa.');
                        return;
                    }

                    if (!confirm('Bạn có chắc chắn muốn xóa sự kiện này không?')) return;

                    try {
                        await deleteEventApi(activeEventId);
                        alert('Đã xóa thành công!');
                        window.closeModal('createEventModal');
                        if (typeof window.loadAllEvents === 'function') {
                            window.loadAllEvents();
                        }
                    } catch (e) {
                        console.error(e);
                        alert(e.message || 'Lỗi kết nối máy chủ.');
                    }
                });

                parentContainer.appendChild(deleteBtn);
            }
        } else {
            const existingDeleteBtn = document.getElementById('dynamicDeleteEventBtn');
            if (existingDeleteBtn) existingDeleteBtn.style.display = 'inline-block';
        }

    if (typeof window.openModal === 'function') {
        window.openModal('createEventModal');
    }
}
