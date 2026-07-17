<script>
    /* ==========================================
       API & UTILS
       ========================================== */
       async function sendRequest(url, method, body = null) {
           const response = await fetch(url, {
               method,
               credentials: 'include', // This is mandatory for session-based API auth
               headers: {
                   'Accept': 'application/json',
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
               },
               body: body ? JSON.stringify(body) : null
           });
           return await response.json();
       }

    /* ==========================================
       MODAL LOGIC
       ========================================== */
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('is-open');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.remove('is-open');

        // Safely reset form if it exists
        const form = document.getElementById(`${id}Form`);
        if (form) form.reset();
    }

    // Close modal when clicking the dark background overlay
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });

    /* ==========================================
       FORM ACTIONS & SUBMISSIONS
       ========================================== */
    function getInstrumentArray(id) {
        const element = document.getElementById(id);
        if (!element) return [];
        const val = element.value;
        return val ? val.split(',').map(i => i.trim()).filter(i => i !== '') : [];
    }

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

        const data = await sendRequest(url, method, payload);
        if (data.status === 'success') {
            window.location.reload();
        } else {
            alert(data.message || 'Operation failed.');
        }
    }

    /* ==========================================
       UI HELPERS & BOUND FUNCTIONS
       ========================================== */
    function prepareAndOpenEditModal(id) {
        if (!id) {
            console.error("No ID was provided to prepareAndOpenEditModal.");
            return;
        }

        const row = document.getElementById(`member-row-${id}`);
        if (!row) {
            console.error(`Could not find table row matching: member-row-${id}`);
            alert("Error: Member data row could not be located.");
            return;
        }

        // Set the hidden input value securely
        const hiddenIdInput = document.getElementById('edit-member-id');
        if (hiddenIdInput) {
            hiddenIdInput.value = id;
        } else {
            console.error("Missing critical hidden input field: 'edit-member-id'");
            return;
        }

        // Safely map values, fallback to empty string if attributes don't exist
        const nameEl = row.querySelector('[data-name]');
        const emailEl = row.querySelector('[data-email]');
        const birthdayEl = row.querySelector('[data-birthday-raw]');
        const roleEl = row.querySelector('[data-role]');
        const instrumentsEl = row.querySelector('[data-instruments-raw]');

        document.getElementById('edit-name').value = nameEl ? nameEl.textContent.trim() : '';
        document.getElementById('edit-email').value = emailEl ? emailEl.textContent.trim() : '';
        document.getElementById('edit-birthday').value = birthdayEl ? (birthdayEl.getAttribute('data-birthday-raw') || '') : '';
        document.getElementById('edit-role').value = roleEl ? (roleEl.getAttribute('data-role') || '') : '';
        document.getElementById('edit-instruments').value = instrumentsEl ? (instrumentsEl.getAttribute('data-instruments-raw') || '') : '';

        openModal('editMemberModal');
    }

    async function submitEditForm(event) {
        event.preventDefault();

        const idInput = document.getElementById('edit-member-id');
        const id = idInput ? idInput.value : '';

        // FRONTEND GUARD: If the ID is completely blank, halt the request before hitting the controller
        if (!id || id.trim() === '') {
            alert("Error: Cannot update member because the record ID is missing from the form.");
            console.error("Aborted update request because 'edit-member-id' value is empty.");
            return;
        }

        const url = `/api/members/${id}`;
        await handleMemberSubmit(event, 'PUT', url, 'editMemberModal');
    }

    async function deleteMember(id) {
        if (!id) {
            alert("Error: Cannot delete member because the target ID is missing.");
            return;
        }
        if (!confirm('Are you absolutely sure you want to delete this member?')) return;

        const data = await sendRequest(`/api/members/${id}`, 'DELETE');
        if (data.status === 'success') {
            window.location.reload();
        } else {
            alert(data.message || 'Deletion failed.');
        }
    }
</script>
