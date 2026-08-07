// =========================================================================
// RENDER DANH SÁCH SỰ KIỆN SIDEBAR & WIDGET SẮP TỚI
// =========================================================================

import { parseLocalDateTime } from './utils.js';
import { deleteEventApi } from './eventApi.js';

export const typeColors = {
    'PRACTICE': '#0369a1',
    'SHOW': '#dc2626',
    'MEETING': '#d97706',
    'EVENT': '#7e22ce',
};

// Render danh sách ở Tab (Confirmed / Poll)
export function renderEventList(targetContainer, data, type) {
    targetContainer.innerHTML = '';
    if (!data || data.length === 0) {
        targetContainer.innerHTML = `<p class="subtitle" style="color: #6c757d; font-style: italic;">Không có ${type === 'confirmed' ? 'lịch đã chốt' : 'khảo sát'} nào.</p>`;
        return;
    }

    data.forEach(ev => {
        const card = document.createElement('div');
        card.className = 'event-card';
        card.style.position = 'relative';

        let eventId = ev._id || ev.id || (ev._id && ev._id.$oid);
        let actionBtn = '';
        let pollConfigStr = ev.poll_config ? JSON.stringify(ev.poll_config).replace(/'/g, "&apos;") : '{}';

        if (ev.status === 'POLL') {
            if (ev.is_manager) {
                actionBtn = `<button class="btn btn-primary w-100 fill-poll-btn" data-id="${eventId}" data-config='${pollConfigStr}'>Điền Lịch Rảnh</button>`;
                actionBtn += `<button class="btn btn-outline w-100 mt-2 view-report-btn" data-id="${eventId}" data-title="${ev.title}" data-config='${pollConfigStr}'>Xem Báo Cáo & Chốt Lịch</button>`;
            } else {
                if (ev.has_submitted_availability) {
                    actionBtn = `<button class="btn w-100 fill-poll-btn" data-id="${eventId}" data-config='${pollConfigStr}' style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 12px; padding: 6px 10px; font-weight: 500; border-radius: 4px;">✓ Đã điền lịch rảnh (Sửa)</button>`;
                } else {
                    actionBtn = `<button class="btn btn-primary w-100 fill-poll-btn" data-id="${eventId}" data-config='${pollConfigStr}'>Điền Lịch Rảnh</button>`;
                }
            }
        }

        let deleteBtnHtml = ev.is_manager ? `<button class="delete-event-btn" data-id="${eventId}" title="Xóa sự kiện" style="position: absolute; top: 12px; right: 12px; background: transparent; border: none; font-size: 20px; font-weight: bold; color: #dc3545; cursor: pointer;">&times;</button>` : '';

        card.innerHTML = `
            ${deleteBtnHtml}
            <div>
                <span class="badge badge-${(ev.type || '').toLowerCase()}">${ev.type || 'N/A'}</span>
                <h4>${ev.title}</h4>
                <div class="event-info">
                    <p style="margin: 4px 0; font-size: 13px; color: #4b5563;"><strong>Trạng thái:</strong> ${ev.status}</p>
                </div>
            </div>
            <div style="margin-top: 15px;">${actionBtn}</div>
        `;
        targetContainer.appendChild(card);
    });
}

// Render widget sự kiện sắp tới / đang diễn ra ở sidebar (Đã bỏ click mở modal)
export function renderUpcomingEvents(allEvents, reloadCallback) {
    const container = document.getElementById('upcomingEventsList');
    if (!container) return;

    const now = new Date();
    const processedEvents = allEvents.map(event => {
        const start = parseLocalDateTime(event.start_time || event.start);
        const end = event.end_time ? parseLocalDateTime(event.end_time) : new Date(start.getTime() + 60 * 60 * 1000);

        let statusType = 'upcoming';
        if (now >= start && now <= end) {
            statusType = 'ongoing';
        } else if (now > end) {
            statusType = 'passed';
        }
        return { ...event, startDate: start, endDate: end, statusType };
    });

    const relevantEvents = processedEvents.filter(event => event.statusType !== 'passed');
    relevantEvents.sort((a, b) => {
        if (a.statusType === 'ongoing' && b.statusType !== 'ongoing') return -1;
        if (a.statusType !== 'ongoing' && b.statusType === 'ongoing') return 1;
        return a.startDate - b.startDate;
    });

    if (relevantEvents.length === 0) {
        container.innerHTML = `<p style="font-size: 12px; color: #888; padding: 4px 8px;">Không có sự kiện nào.</p>`;
        return;
    }

    container.innerHTML = relevantEvents.map(event => {
        const d = event.startDate;
        const formattedDate = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} - ${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
        const eventTypeKey = (event.type || '').toUpperCase();
        const dotColor = typeColors[eventTypeKey] || '#6366f1';
        const eventId = event.id || event.event_id || event._id;

        // Đã đổi cursor thành 'default' vì không bấm vào để sửa nữa
        return `
            <div class="sidebar-upcoming-card" data-event-id="${eventId}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 8px; cursor: default; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: flex-start; gap: 10px; flex-grow: 1; overflow: hidden; margin-right: 8px;">
                    <span style="background-color: ${dotColor}; flex-shrink: 0; width: 9px; height: 9px; border-radius: 50%; margin-top: 5px;"></span>
                    <div style="display: flex; flex-direction: column; overflow: hidden;">
                        <span style="font-weight: 600; font-size: 13.5px; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${event.title}</span>
                        <span style="font-size: 11.5px; color: #64748b; margin-top: 3px;">📅 ${formattedDate}</span>
                    </div>
                </div>
                <button type="button" class="sidebar-delete-btn" data-id="${eventId}" title="Xóa sự kiện" style="background: none; border: none; color: #9ca3af; font-size: 18px; cursor: pointer; padding: 4px; line-height: 1; flex-shrink: 0;">&times;</button>
            </div>
        `;
    }).join('');

    // Chỉ giữ lại logic bấm nút Xóa (`&times;`) ở sidebar
    container.onclick = null;
    container.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.sidebar-delete-btn');
        if (deleteBtn) {
            e.stopPropagation();
            const eventId = deleteBtn.getAttribute('data-id');
            if (!confirm('Bạn có chắc chắn muốn xóa mục này không?')) return;

            deleteEventApi(eventId).then(() => {
                alert('Đã xóa thành công!');
                if (reloadCallback) reloadCallback();
            }).catch(err => {
                console.error(err);
                alert(err.message || 'Lỗi kết nối máy chủ.');
            });
        }
    });
}

export function bindDeleteEventListeners(reloadCallback) {
    document.querySelectorAll('.delete-event-btn').forEach(btn => {
        if (btn.dataset.deleteListenerAttached) return;
        btn.dataset.deleteListenerAttached = "true";

        btn.addEventListener('click', async function () {
            const eventId = this.getAttribute('data-id');
            if (!confirm('Bạn có chắc chắn muốn xóa mục này không?')) return;

            try {
                await deleteEventApi(eventId);
                alert('Đã xóa thành công!');
                if (reloadCallback) reloadCallback();
            } catch (e) {
                console.error(e);
                alert(e.message || 'Lỗi kết nối máy chủ.');
            }
        });
    });
}
