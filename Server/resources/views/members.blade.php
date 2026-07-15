@extends('components/layout')

@section('title', 'Members Roster | Sunburst')

@section('content')
<div class="roster-container">
    <div class="roster-header">
        <div>
            <h3 class="roster-title">Active Roster</h3>
            <p class="roster-subtitle">Manage and view performing band members inside MongoDB.</p>
        </div>
        <span class="roster-badge-count">
            {{ count($members) }} Members Active
        </span>
    </div>

    <div class="roster-card">
        <div class="roster-table-wrapper">
            <table class="roster-table">
                <thead>
                    <tr>
                        <th>Name & Email</th>
                        <th>Role</th>
                        <th>Instruments</th>
                        <th>Joined Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr id="member-row-{{ $member->_id }}">
                            <td>
                                <div class="user-identity">
                                    <span class="user-name" data-name>{{ $member->name ?? 'N/A' }}</span>
                                    <span class="user-email" data-email>{{ $member->email ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                @if(strtolower($member->role ?? 'member') === 'admin')
                                    <span class="roster-badge roster-badge-admin" data-role="admin">Admin</span>
                                @else
                                    <span class="roster-badge roster-badge-member" data-role="member">Member</span>
                                @endif
                            </td>
                            <td>
                                <div class="instrument-tags" data-instruments-raw="{{ implode(', ', (array)($member->instrument ?? [])) }}">
                                    @if(!empty($member->instrument))
                                        @foreach ((array)$member->instrument as $inst)
                                            <span class="instrument-tag">{{ trim($inst) }}</span>
                                        @endforeach
                                    @else
                                        <span class="no-instruments">No instruments assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="roster-date">
                                    {{ !empty($member->joined_in) ? date('M d, Y', strtotime($member->joined_in)) : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="roster-actions">
                                    <button onclick="prepareAndOpenEditModal('{{ $member->_id }}')" class="action-edit" style="background:none; border:none; cursor:pointer;">
                                        Edit
                                    </button>
                                    <button onclick="deleteMember('{{ $member->_id }}')" class="action-delete">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px 0; color: #64748b;">
                                No members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Reusable Modal Component --}}
<x-modal id="editMemberModal" title="Edit Band Member" submitFn="submitEditForm(event)">
    <input type="hidden" id="edit-member-id">

    <div class="form-group">
        <label class="form-label" for="edit-name">Display Name</label>
        <input type="text" id="edit-name" class="form-input" required placeholder="e.g. Alex Johnson">
    </div>

    <div class="form-group">
        <label class="form-label" for="edit-email">Email Address</label>
        <input type="email" id="edit-email" class="form-input" required placeholder="e.g. name@domain.com">
    </div>

    <div class="form-group">
        <label class="form-label" for="edit-role">Role Hierarchy</label>
        <select id="edit-role" class="form-input" style="height: 42px;">
            <option value="member">Member</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="edit-instruments">Instruments (Comma separated)</label>
        <input type="text" id="edit-instruments" class="form-input" placeholder="e.g. Vocal, Bass Guitar, Synth">
    </div>

    {{-- Footer Actions Slot --}}
    <x-slot name="footer">
        <button type="submit" class="btn-save">Save Changes</button>
    </x-slot>
</x-modal>

<script>
    // 1. Reusable Modal Helpers
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('open');
            modal.addEventListener('click', function clickOutside(e) {
                if (e.target === modal) {
                    closeModal(id);
                    modal.removeEventListener('click', clickOutside);
                }
            });
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('open');
        }
    }

    // 2. Prepare member data and trigger openModal
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

    // 3. Submit Update
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
        .catch(err => console.error('Error:', err));
    }

    // 4. Delete Member Record
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
            .catch(err => {
                console.error('Delete Request Error:', err);
                alert('Network error. Check your server connection.');
            });
        }
    }
</script>
@endsection
