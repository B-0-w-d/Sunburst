// =========================================================================
// QUẢN LÝ KÉO THẢ THÀNH VIÊN TRONG FORM TẠO SỰ KIỆN
// =========================================================================

let draggedMemberElement = null;

export function handleMemberDragStart(e) {
    draggedMemberElement = e.currentTarget;
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.id);
}

export function handleMemberDrop(e, targetZoneType) {
    e.preventDefault();
    if (!draggedMemberElement) return;

    const targetZone = targetZoneType === 'selected'
        ? document.getElementById('selectedMembersZone')
        : document.getElementById('availableMembersZone');

    if (!targetZone) return;

    if (targetZoneType === 'selected' && !draggedMemberElement.querySelector('.btn-remove-chip')) {
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-remove-chip';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function() { removeMemberChip(this); };
        draggedMemberElement.appendChild(removeBtn);
    } else if (targetZoneType === 'available') {
        const btn = draggedMemberElement.querySelector('.btn-remove-chip');
        if (btn) btn.remove();
    }

    targetZone.appendChild(draggedMemberElement);
    updateMemberHiddenInput();
    draggedMemberElement = null;
}

export function removeMemberChip(btn) {
    const chip = btn.closest('.member-chip');
    const availableZone = document.getElementById('availableMembersZone');
    if (chip && availableZone) {
        btn.remove();
        availableZone.appendChild(chip);
        updateMemberHiddenInput();
    }
}

export function updateMemberHiddenInput() {
    const selectedZone = document.getElementById('selectedMembersZone');
    if (!selectedZone) return;
    const chips = selectedZone.querySelectorAll('.member-chip');
    const ids = Array.from(chips).map(chip => chip.dataset.id);

    const hiddenInput = document.getElementById('targetMemberIds');
    if (hiddenInput) {
        hiddenInput.value = ids.join(',');
    }
}
