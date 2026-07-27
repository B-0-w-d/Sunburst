/**
 * Khởi tạo Component Nhạc cụ Drag & Drop + Add Custom
 */
export function initInstrumentSelector() {
    const containers = document.querySelectorAll('[data-component="instrument-selector"]');

    containers.forEach(container => {
        const availableZone = container.querySelector('.available-zone');
        const selectedZone = container.querySelector('.selected-zone');
        const input = container.querySelector('.instrument-custom-input');
        const addBtn = container.querySelector('.btn-add-instrument');

        // Hàm hỗ trợ tạo chip mới
        const createChip = (name) => {
            const chip = document.createElement('div');
            chip.className = 'instrument-chip';
            chip.draggable = true;
            chip.setAttribute('data-value', name);
            chip.innerHTML = `
                <span class="chip-label">${name}</span>
                <button type="button" class="btn-remove-chip">&times;</button>
            `;
            attachChipEvents(chip);
            return chip;
        };

        // Hàm thêm nhạc cụ tùy chỉnh vào vùng đã chọn
        const handleAddCustomInstrument = () => {
            if (!input) return;
            const val = input.value.trim();
            if (!val) return;

            // Kiểm tra trùng lặp trong cả 2 vùng
            const existingChips = container.querySelectorAll('.instrument-chip');
            const exists = Array.from(existingChips).some(
                chip => chip.getAttribute('data-value').toLowerCase() === val.toLowerCase()
            );

            if (exists) {
                alert('Nhạc cụ này đã có trong danh sách!');
                return;
            }

            // Tạo chip mới và đưa thẳng vào vùng "Đã chọn"
            const newChip = createChip(val);
            selectedZone.appendChild(newChip);
            input.value = '';
        };

        // Event listener cho nút + và phím Enter
        if (addBtn) addBtn.addEventListener('click', handleAddCustomInstrument);
        if (input) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleAddCustomInstrument();
                }
            });
        }

        // Bắt sự kiện xóa / click cho toàn bộ chip trong container (Event Delegation)
        container.addEventListener('click', (e) => {
            // Click nút xóa ×
            if (e.target.classList.contains('btn-remove-chip')) {
                const chip = e.target.closest('.instrument-chip');
                if (chip) chip.remove();
                return;
            }

            // Click trực tiếp vào chip để chuyển vùng nhanh
            const chip = e.target.closest('.instrument-chip');
            if (chip && !e.target.classList.contains('btn-remove-chip')) {
                const parentZone = chip.parentElement;
                if (parentZone.classList.contains('available-zone')) {
                    // Chuyển sang selected zone
                    if (!chip.querySelector('.btn-remove-chip')) {
                        chip.insertAdjacentHTML('beforeend', '<button type="button" class="btn-remove-chip">&times;</button>');
                    }
                    selectedZone.appendChild(chip);
                } else if (parentZone.classList.contains('selected-zone')) {
                    // Chuyển về available zone
                    const removeBtn = chip.querySelector('.btn-remove-chip');
                    if (removeBtn) removeBtn.remove();
                    availableZone.appendChild(chip);
                }
            }
        });

        // Xử lý Drag and Drop
        const zones = [availableZone, selectedZone];
        let draggedChip = null;

        function attachChipEvents(chip) {
            chip.addEventListener('dragstart', (e) => {
                draggedChip = chip;
                chip.classList.add('dragging');
                e.dataTransfer.setData('text/plain', chip.getAttribute('data-value'));
            });

            chip.addEventListener('dragend', () => {
                draggedChip = null;
                chip.classList.remove('dragging');
            });
        }

        container.querySelectorAll('.instrument-chip').forEach(attachChipEvents);

        zones.forEach(zone => {
            if (!zone) return;

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('drag-over');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                if (draggedChip) {
                    if (zone.classList.contains('selected-zone')) {
                        if (!draggedChip.querySelector('.btn-remove-chip')) {
                            draggedChip.insertAdjacentHTML('beforeend', '<button type="button" class="btn-remove-chip">&times;</button>');
                        }
                    } else {
                        const removeBtn = draggedChip.querySelector('.btn-remove-chip');
                        if (removeBtn) removeBtn.remove();
                    }
                    zone.appendChild(draggedChip);
                }
            });
        });
    });
}

/**
 * Lấy mảng danh sách nhạc cụ đã chọn
 */
export function getSelectedInstruments(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return [];

    const selectedZone = container.querySelector('.selected-zone');
    if (!selectedZone) return [];

    const chips = selectedZone.querySelectorAll('.instrument-chip');
    return Array.from(chips).map(chip => chip.getAttribute('data-value'));
}
