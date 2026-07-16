{{-- Inject the JavaScript code directly --}}
    @push('scripts')
    <script>
        // Global variable to keep track of the backdrop listener without stacking
        let activeClickOutsideHandler = null;

        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            modal.classList.add('open');

            if (activeClickOutsideHandler) {
                modal.removeEventListener('click', activeClickOutsideHandler);
            }

            activeClickOutsideHandler = function clickOutside(e) {
                if (e.target === modal) {
                    closeModal(id);
                }
            };
            modal.addEventListener('click', activeClickOutsideHandler);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            modal.classList.remove('open');

            const form = document.getElementById(`${id}Form`);
            if (form && id === 'addMemberModal') {
                form.reset();
            }

            if (activeClickOutsideHandler) {
                modal.removeEventListener('click', activeClickOutsideHandler);
                activeClickOutsideHandler = null;
            }
        }

        /* ==========================================
           CREATE MEMBER FUNCTION
           ========================================== */
        function submitAddForm(event) {
            event.preventDefault();

            const instrumentsRaw = document.getElementById('add-instruments').value;
            const instrumentsArray = instrumentsRaw
                ? instrumentsRaw.split(',').map(item => item.trim()).filter(item => item !== '')
                : [];

            const payload = {
                name: document.getElementById('add-name').value,
                email: document.getElementById('add-email').value,
                role: document.getElementById('add-role').value,
                instrument: instrumentsArray
            };

            fetch('/api/members', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal('addMemberModal');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error creating a new record.');
                }
            })
            .catch(err => console.error('Add Operations Error:', err));
        }

        /* ==========================================
           EDIT MEMBER FUNCTIONS
           ========================================== */
        function prepareAndOpenEditModal(id) {
            const row = document.getElementById(`member-row-${id}`);
            const name = row.querySelector('[data-name]').textContent.trim();
            const email = row.querySelector('[data-email]').textContent.trim();
            const role = row.querySelector('[data-role]').getAttribute('data-role');
            const instruments = row.querySelector('[data-instruments-raw]').getAttribute('data-instruments-raw');

            document.getElementById('edit-member-id').value = id;
            document.getElementById('edit-name').value = name === 'N/A' ? '' : name;
            document.getElementById('edit-email').value = email === 'N/A' ? '' : email;
            document.getElementById('edit-role').value = role;
            document.getElementById('edit-instruments').value = instruments;

            openModal('editMemberModal');
        }

        function submitEditForm(event) {
            event.preventDefault();
            const id = document.getElementById('edit-member-id').value;
            const instrumentsRaw = document.getElementById('edit-instruments').value;
            const instrumentsArray = instrumentsRaw ? instrumentsRaw.split(',').map(item => item.trim()).filter(item => item !== '') : [];

            const payload = {
                name: document.getElementById('edit-name').value,
                email: document.getElementById('edit-email').value,
                role: document.getElementById('edit-role').value,
                instrument: instrumentsArray
            };

            fetch(`/api/members/${id}`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal('editMemberModal');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error updating member details.');
                }
            })
            .catch(err => console.error('Update Request Error:', err));
        }

        /* ==========================================
           DELETE MEMBER FUNCTION
           ========================================== */
        function deleteMember(id) {
            if (confirm('Are you absolutely sure you want to delete this member?')) {
                fetch(`/api/members/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(data.message || 'An error occurred while deleting the member.');
                    }
                })
                .catch(err => console.error('Delete Request Error:', err));
            }
        }
    </script>
    @endpush
