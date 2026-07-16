<x-layout>
    <x-slot name="title">Members Roster | Sunburst</x-slot>

    <div class="dashboard-layout-wrapper" style="display: flex; gap: 32px; max-width: 100%; max-height: 100%; align-items: flex-start;">

        <aside class="nav-sidebar" style="width: 260px; flex-shrink: 0;">
            <div class="sidebar-section">
                <div class="section-header">
                    <span class="section-title">Projects</span>
                </div>
                <div class="project-list">
                    <a href="#" class="project-item">
                        <span class="dot dot-blue"></span> Campaigns
                    </a>
                    <a href="#" class="project-item active">
                        <span class="dot dot-red"></span> Publications
                    </a>
                    <a href="#" class="project-item">
                        <span class="dot dot-green"></span> Development
                    </a>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-header">
                    <span class="section-title">Members</span>
                    <button class="add-btn" onclick="openModal('addMemberModal')">+</button>
                </div>
                <div class="member-list">
                    @forelse ($members as $member)
                        <div class="member-item">
                            <x-icons.user />
                            <span>{{ $member->name ?? 'N/A' }}</span>
                        </div>
                    @empty
                        <div class="member-item" style="color: #64748b; font-size: 0.85rem; justify-content: center;">
                            No members
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="promo-card">
                <span class="promo-tag">Unobvious Tips</span>
                <h4 class="promo-title">DEO BIET NEN LAM GI O DAY</h4>
                <p class="promo-meta">3 min read</p>
                <a href="#" class="promo-btn">
                    Read post <span class="arrow">→</span>
                </a>
            </div>
        </aside>

        <div class="roster-container" style="flex-grow: 1; width: 100%; margin: 0; padding: 0;">
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
                                                <x-icons.edit/>
                                            </button>
                                            <button onclick="deleteMember('{{ $member->_id }}')" class="action-delete">
                                                <x-icons.delete/>
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
    </div>

    <x-modal id="addMemberModal" title="Add New Band Member" submitFn="submitAddForm(event)">
        <div class="form-group">
            <label class="form-label" for="add-name">Display Name</label>
            <input type="text" id="add-name" class="form-input" required placeholder="e.g. Ren Nguyen">
        </div>

        <div class="form-group">
            <label class="form-label" for="add-email">Email Address</label>
            <input type="email" id="add-email" class="form-input" required placeholder="e.g. Rendarapper@gmail.com">
        </div>

        <div class="form-group">
            <label class="form-label" for="add-role">Role Hierarchy</label>
            <select id="add-role" class="form-input" style="height: 42px;">
                <option value="member" selected>Member</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="add-instruments">Instruments (Comma separated)</label>
            <input type="text" id="add-instruments" class="form-input" placeholder="e.g. Vocal, Bass Guitar">
        </div>

        <x-slot name="footer">
            <button type="submit" class="btn-save">Create Member</button>
        </x-slot>
    </x-modal>

    <x-modal id="editMemberModal" title="Edit Band Member" submitFn="submitEditForm(event)">
        <input type="hidden" id="edit-member-id">

        <div class="form-group">
            <label class="form-label" for="edit-name">Display Name</label>
            <input type="text" id="edit-name" class="form-input" required placeholder="e.g. Ren Nguyen">
        </div>

        <div class="form-group">
            <label class="form-label" for="edit-email">Email Address</label>
            <input type="email" id="edit-email" class="form-input" required placeholder="e.g. Rendarapper@gmail.com">
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

        <x-slot name="footer">
            <button type="submit" class="btn-save">Save Changes</button>
        </x-slot>
    </x-modal>

    {{-- Inject cleaner segmented scripts via partial include mapping flags --}}
    @push('scripts')
        @include('scripts.membersScript')
    @endpush
</x-layout>
