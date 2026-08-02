// =========================================================================
// DỰNG BẢNG MA TRẬN KHẢO SÁT, HEATMAP VÀ CHỐT LỊCH
// =========================================================================

export function buildPollMatrix(config, tableId, isReadonly = false, slotStats = null) {
    const table = document.getElementById(tableId);
    if (!table) return;
    table.innerHTML = '';

    // Hàm phụ parse ngày tháng dạng YYYY-MM-DD theo giờ cục bộ (local time) tránh bị lệch múi giờ UTC
    const parseLocalDate = (dateStr) => {
        if (!dateStr) return new Date();
        const cleanStr = dateStr.split('T')[0];
        const [y, m, d] = cleanStr.split('-').map(Number);
        return new Date(y || new Date().getFullYear(), (m || 1) - 1, d || 1);
    };

    const startDate = config.start_date ? parseLocalDate(config.start_date) : new Date();
    const endDate = config.end_date ? parseLocalDate(config.end_date) : new Date();
    const step = config.step_minutes || 30;

    let dates = [];
    let curr = new Date(startDate);
    while (curr <= endDate) {
        dates.push(new Date(curr));
        curr.setDate(curr.getDate() + 1);
    }

    let timeSlots = [];
    let totalMinutesStart = 6 * 60;
    let totalMinutesEnd = 24 * 60;
    for (let m = totalMinutesStart; m < totalMinutesEnd; m += step) {
        let hh = String(Math.floor(m / 60)).padStart(2, '0');
        let mm = String(m % 60).padStart(2, '0');
        timeSlots.push(`${hh}:${mm}`);
    }
    timeSlots.push("24:00");

    let headerRow1 = '<tr><th class="time-col-header"></th>';
    let headerRow2 = '<tr><th class="time-sub-header">Thời gian</th>';

    dates.forEach((d, index) => {
        let dayName = d.toLocaleDateString('vi-VN', { weekday: 'short' });
        let dayMonth = `${d.getDate()}/${d.getMonth() + 1}`;

        headerRow1 += `<th>${dayName}</th>`;

        if (!isReadonly) {
            headerRow2 += `<th>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <span>${dayMonth}</span>
                    <button type="button" class="select-col-btn" data-col-index="${index}" style="font-size: 11px; padding: 2px 6px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: #f8f9fa;">Chọn cả ngày</button>
                </div>
            </th>`;
        } else {
            headerRow2 += `<th>${dayMonth}</th>`;
        }
    });
    headerRow1 += '</tr>';
    headerRow2 += '</tr>';
    table.innerHTML = headerRow1 + headerRow2;

    for (let i = 0; i < timeSlots.length; i++) {
        let time = timeSlots[i];
        let row = '<tr>';

        if (time === "24:00") {
            row += `<td class="time-label-cell"><span>12:00 AM</span></td>`;
        } else {
            let [hh, mm] = time.split(':').map(Number);
            let hour12 = hh === 0 ? 12 : (hh > 12 ? hh - 12 : hh);
            let period = hh >= 12 ? 'PM' : 'AM';
            let timeFormatted = `${hour12}:${String(mm).padStart(2, '0')} ${period}`;
            row += `<td class="time-label-cell"><span>${timeFormatted}</span></td>`;
        }

        dates.forEach((d, index) => {
            // Định dạng chuỗi ngày an toàn theo giờ cục bộ (YYYY-MM-DD)
            const pad = (n) => String(n).padStart(2, '0');
            let dateStr = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            let slotISO = `${dateStr}T${time === "24:00" ? "00:00" : time}:00`;

            if (isReadonly) {
                let count = slotStats ? (slotStats[slotISO] || 0) : 0;
                let heatmapClass = count > 0 ? (count >= 3 ? 'heatmap-high' : 'heatmap-mid') : 'heatmap-low';
                row += `<td class="time-slot-cell ${heatmapClass} selectable-slot" data-slot="${slotISO}">
                            <span style="pointer-events: none; display: block; width: 100%; height: 100%;">${count}</span>
                        </td>`;
            } else {
                let borderClass = (time !== "24:00" && time.endsWith('30')) ? 'slot-half-hour' : 'slot-full-hour';
                row += `<td class="time-slot-cell ${borderClass}" data-col-index="${index}" data-slot="${slotISO}"></td>`;
            }
        });

        row += '</tr>';
        table.innerHTML += row;
    }

    if (!isReadonly) {
        // Nút chọn cả ngày
        table.querySelectorAll('.select-col-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const colIndex = this.getAttribute('data-col-index');
                const colCells = table.querySelectorAll(`.time-slot-cell[data-col-index="${colIndex}"]`);

                const selectedCount = Array.from(colCells).filter(c => c.classList.contains('selected')).length;
                const shouldSelect = selectedCount < colCells.length;

                colCells.forEach(cell => cell.classList.toggle('selected', shouldSelect));

                this.innerText = shouldSelect ? 'Bỏ chọn' : 'Chọn cả ngày';
                this.style.background = shouldSelect ? '#007bff' : '#f8f9fa';
                this.style.color = shouldSelect ? '#fff' : '#000';
            });
        });

        // Kích hoạt kéo chuột chọn ô cho bảng khảo sát thông thường
        initTableDragSelection(table, false);
    } else {
        // Kích hoạt kéo chuột chọn ô cho bảng báo cáo / chốt lịch (isReadonly = true)
        initTableDragSelection(table, true);
    }
}

// Cơ chế kéo chuột bôi đen mượt mà cho cả 2 chế độ
function initTableDragSelection(table, isReportMode = false) {
    let isMouseDown = false;
    let isSelecting = true;

    if (window._globalMouseUpHandler) {
        document.removeEventListener('mouseup', window._globalMouseUpHandler);
    }

    window._globalMouseUpHandler = () => {
        isMouseDown = false;
    };
    document.addEventListener('mouseup', window._globalMouseUpHandler);

    table.addEventListener('mousedown', (e) => {
        const cell = e.target.closest('.time-slot-cell');
        if (!cell) return;

        isMouseDown = true;
        const checkClass = isReportMode ? 'selected-final-slot' : 'selected';
        isSelecting = !cell.classList.contains(checkClass);

        if (isReportMode) {
            cell.classList.toggle('selected', isSelecting);
            cell.classList.toggle('selected-final-slot', isSelecting);
        } else {
            cell.classList.toggle('selected', isSelecting);
        }

        e.preventDefault();
    });

    table.addEventListener('mouseover', (e) => {
        if (!isMouseDown) return;
        const cell = e.target.closest('.time-slot-cell');
        if (!cell) return;

        if (isReportMode) {
            cell.classList.toggle('selected', isSelecting);
            cell.classList.toggle('selected-final-slot', isSelecting);
        } else {
            cell.classList.toggle('selected', isSelecting);
        }
    });
}

export function buildHeatmapMatrix(config, slotStats, targetCount) {
    buildPollMatrix(config, 'reportMatrixTable', true, slotStats);
}
