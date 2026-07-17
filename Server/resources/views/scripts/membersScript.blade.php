<script>
    /* ==========================================
       API & UTILS
       ========================================== */
    async function sendRequest(url, method, body = null) {
        const response = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: body ? JSON.stringify(body) : null
        });
        return response.json();
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

               // Reset form if it exists
               const form = document.getElementById(`${id}Form`);
               if (form) form.reset();
           }

           // Close when clicking the dark background (the .modal div itself)
           window.addEventListener('click', (e) => {
               if (e.target.classList.contains('modal')) {
                   closeModal(e.target.id);
               }
           });

    /* ==========================================
       FORM ACTIONS
       ========================================== */
    function getInstrumentArray(id) {
        const val = document.getElementById(id).value;
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

        try {
            const data = await sendRequest(url, method, payload);
            if (data.status === 'success') {
                window.location.reload();
            } else {
                alert(data.message || 'Operation failed.');
            }
        } catch (err) {
            console.error('Request Error:', err);
        }
    }

    /* ==========================================
       UI HELPERS
       ========================================== */
    function prepareAndOpenEditModal(id) {
        const row = document.getElementById(`member-row-${id}`);
        document.getElementById('edit-member-id').value = id;
        document.getElementById('edit-name').value = row.querySelector('[data-name]').textContent.trim();
        document.getElementById('edit-email').value = row.querySelector('[data-email]').textContent.trim();
        document.getElementById('edit-birthday').value = row.querySelector('[data-birthday-raw]').getAttribute('data-birthday-raw') || '';
        document.getElementById('edit-role').value = row.querySelector('[data-role]').getAttribute('data-role');
        document.getElementById('edit-instruments').value = row.querySelector('[data-instruments-raw]').getAttribute('data-instruments-raw');

        openModal('editMemberModal');
    }

    async function deleteMember(id) {
        if (!confirm('Are you absolutely sure you want to delete this member?')) return;

        const data = await sendRequest(`/api/members/${id}`, 'DELETE');
        if (data.status === 'success') window.location.reload();
        else alert(data.message || 'Deletion failed.');
    }
</script>
