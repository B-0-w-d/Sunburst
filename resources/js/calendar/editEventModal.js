// =========================================================================
// XỬ LÝ MODAL CHỈNH SỬA VÀ XÓA SỰ KIỆN (EDIT / DELETE)
// =========================================================================

import { deleteEventApi, updateEventApi } from './eventApi.js';

// Hàm xử lý ẩn/hiện cấu hình trong Modal Chỉnh Sửa theo trạng thái
window.handleEditEventStatusChange = function(selectElem) {
    const pollSection = document.getElementById('editPollConfigSection');
    const confirmedSection = document.getElementById('editConfirmedConfigSection');
    const layoutWrapper = document.getElementById('editModalLayoutWrapper');

    if (!pollSection || !confirmedSection || !layoutWrapper) return;

    if (selectElem.value === 'CONFIRMED') {
        pollSection.style.display = 'none';
        confirmedSection.style.display = 'block';
        layoutWrapper.style.gridTemplateColumns = '1fr';
        layoutWrapper.style.gap = '20px';
    } else {
        pollSection.style.display = 'block';
        confirmedSection.style.display = 'none';
        layoutWrapper.style.gridTemplateColumns = '1fr 1fr';
        layoutWrapper.style.gap = '20px';
    }
};

// Hàm lọc nhạc cụ cho Modal Chỉnh Sửa
window.filterEditAvailableMembers = function(instrument) {
    const container = document.getElementById('editEventMemberSelector');
    if (!container) return;
    const items = container.querySelectorAll('.member-checkbox-item, .member-select-item, label');
    items.forEach(item => {
        if (!instrument) {
            item.style.display = '';
        } else {
            const instAttr = item.getAttribute('data-instrument') || '';
            if (instAttr.includes(instrument)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        }
    });
};

document.addEventListener('DOMContentLoaded', function () {
    // 1. Nút chọn tất cả thành viên trong Modal Sửa
    const editSelectAllBtn = document.getElementById('editSelectAllMembersBtn');
    if (editSelectAllBtn) {
        editSelectAllBtn.addEventListener('click', function () {
            const container = document.getElementById('editEventMemberSelector');
            if (!container) return;
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => {
                if (cb.offsetParent !== null) {
                    cb.checked = true;
                }
            });
        });
    }

    // 2. Xử lý logic khi click vào sự kiện trên lịch tuần để mở Modal Chỉnh Sửa
    window.handleWeeklyEventClickForEdit = function(ev) {
        const activeEventId = ev._id || ev.id || (ev._id && ev._id.$oid);

        const editForm = document.getElementById('editEventForm');
        if (editForm) editForm.dataset.editingId = activeEventId;

        // --- SỬA Ở ĐÂY: Dùng selector chuẩn xác dựa vào form hoặc id riêng của modal sửa ---
        const editModalTitle = document.querySelector('#editEventModal h3, #editEventForm').closest('.modal-content, .modal')?.querySelector('h3');

        // Hoặc cách an toàn nhất: Gán trực tiếp ID cho thẻ h3 của modal sửa thành id="editModalTitleText" trong HTML rồi gọi:
        const titleTextElem = document.getElementById('editModalTitleText');
        if (titleTextElem) {
            titleTextElem.textContent = `Chỉnh Sửa: ${ev.title || 'Sự kiện'}`;
        }

        const titleInput = document.getElementById('editEventTitle');
        if (titleInput) titleInput.value = ev.title || '';

        const typeSelect = document.getElementById('editEventType');
        if (typeSelect) typeSelect.value = ev.type || 'PRACTICE';

        const statusSelect = document.getElementById('editEventStatus');
        if (statusSelect) {
            statusSelect.value = ev.status || 'CONFIRMED';
            statusSelect.dispatchEvent(new Event('change'));
        }

        const startInput = document.getElementById('editStartTime');
        const endInput = document.getElementById('editEndTime');
        if (ev.start_time && startInput) {
            startInput.value = ev.start_time.replace(' ', 'T').substring(0, 16);
        }
        if (ev.end_time && endInput) {
            endInput.value = ev.end_time.replace(' ', 'T').substring(0, 16);
        }

        if (ev.status === 'POLL' && ev.poll_config) {
            const pStart = document.getElementById('editPollStartDate');
            const pEnd = document.getElementById('editPollEndDate');
            const dStart = document.getElementById('editDailyStartTime');
            const dEnd = document.getElementById('editDailyEndTime');

            if (pStart) pStart.value = ev.poll_config.start_date || '';
            if (pEnd) pEnd.value = ev.poll_config.end_date || '';
            if (dStart) dStart.value = ev.poll_config.daily_start_time || '06:00';
            if (dEnd) dEnd.value = ev.poll_config.daily_end_time || '23:59';
        }

        const targetIds = ev.target_member_ids || [];
        const container = document.getElementById('editEventMemberSelector');
        if (container) {
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => {
                cb.checked = targetIds.includes(cb.value) || targetIds.includes(String(cb.value));
            });
        }

        if (typeof window.openModal === 'function') {
            window.openModal('editEventModal');
        }
    };

    // 3. Xử lý nút Xóa sự kiện ngay bên trong Modal Chỉnh Sửa
    const deleteInModalBtn = document.getElementById('deleteEventInModalBtn');
    if (deleteInModalBtn) {
        deleteInModalBtn.addEventListener('click', async function () {
            const editForm = document.getElementById('editEventForm');
            const eventId = editForm?.dataset.editingId;

            if (!eventId) {
                alert('Không tìm thấy ID sự kiện để xóa.');
                return;
            }

            if (!confirm('Bạn có chắc chắn muốn xóa sự kiện này không?')) return;

            try {
                await deleteEventApi(eventId);
                alert('Đã xóa thành công!');
                window.closeModal('editEventModal');
                if (typeof loadAllEvents === 'function') loadAllEvents();
            } catch (e) {
                console.error(e);
                alert(e.message || 'Lỗi kết nối máy chủ.');
            }
        });
    }

    // 4. Xử lý Submit Form Cập Nhật Sự Kiện
    const editEventForm = document.getElementById('editEventForm');
    if (editEventForm && !editEventForm.dataset.listenerAttached) {
        editEventForm.dataset.listenerAttached = "true";

        editEventForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const eventId = this.dataset.editingId;
            if (!eventId) {
                alert('Không tìm thấy ID sự kiện.');
                return;
            }

            const statusVal = document.getElementById('editEventStatus').value;

            const memberContainer = document.getElementById('editEventMemberSelector');
            let targetMemberIds = [];
            if (memberContainer) {
                const checkedBoxes = memberContainer.querySelectorAll('input[type="checkbox"]:checked');
                targetMemberIds = Array.from(checkedBoxes).map(cb => cb.value);
            }

            const remindCheckboxes = document.querySelectorAll('input[name="editRemindMinutes"]:checked');
            const remindMinutesArray = Array.from(remindCheckboxes).map(cb => parseInt(cb.value, 10));

            let payload = {
                title: document.getElementById('editEventTitle').value,
                type: document.getElementById('editEventType').value,
                status: statusVal,
                target_member_ids: targetMemberIds,
                notification_settings: {
                    enabled: remindMinutesArray.length > 0,
                    remind_before_minutes: remindMinutesArray
                }
            };

            if (statusVal === 'POLL') {
                payload.poll_config = {
                    start_date: document.getElementById('editPollStartDate').value,
                    end_date: document.getElementById('editPollEndDate').value,
                    daily_start_time: document.getElementById('editDailyStartTime').value,
                    daily_end_time: document.getElementById('editDailyEndTime').value,
                    step_minutes: 30
                };
            } else {
                const allDayChk = document.getElementById('editAllDayEventCheckbox');
                let startVal = document.getElementById('editStartTime').value;
                let endVal = document.getElementById('editEndTime').value;

                if (allDayChk && allDayChk.checked) {
                    if (!endVal || endVal < startVal) endVal = startVal;
                    startVal = `${startVal} 00:00:00`;
                    endVal = `${endVal} 23:59:59`;
                }

                payload.start_time = startVal;
                payload.end_time = endVal;
            }

            try {
                await updateEventApi(eventId, payload);
                alert('Cập nhật thành công!');
                window.closeModal('editEventModal');
                if (typeof loadAllEvents === 'function') loadAllEvents();
            } catch (ex) {
                console.error(ex);
                alert(ex.message || 'Lỗi kết nối máy chủ.');
            }
        });
    }
});
