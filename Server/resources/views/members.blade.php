<x-navbar>
    @vite(['resources/js/app.js'])
    <x-slot name="title">Members | Sunburst</x-slot>

    <div class="dashboard-layout-wrapper" style="display: flex; gap: 32px; max-width: 100%; max-height: 100%; align-items: flex-start;">

        <aside class="nav-sidebar">
            {{-- Danh sách các dự án sắp tới --}}
            <div class="sidebar-section">
                <div class="section-header">
                    <span class="section-title">Upcomming shows</span>
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

            {{-- Danh sách thành viên và các chức năng quản lý --}}
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

                {{-- Chức năng chỉ hiển thị cho Management Tier (Admin, President, v.v.) --}}
                @if(auth()->user()->isManagementTier())
                    <div class="management-controls" style="padding: 15px 0 0 0; border-top: 1px solid #e2e8f0; margin-top: 10px;">
                        <button type="button"
                                onclick="generateActivationKey()"
                                class="btn-primary"
                                style="width: 100%; padding: 8px; font-size: 0.8rem; background: #cc0000; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                    Xuất Key Đăng ký
                        </button>

                        {{-- Ô input hiển thị mã và nút Copy --}}
                        <div style="margin-top: 10px; display: flex; gap: 5px;">
                            <input type="text" id="key-display" readonly
                                   style="flex-grow: 1; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; font-family: monospace; font-size: 0.9rem;"
                                   placeholder="">
                            <button type="button" onclick="copyToClipboard()"
                                    style="padding: 5px 10px; background: #edf2f7; border: 1px solid #cbd5e0; border-radius: 4px; cursor: pointer;">
                                <x-icons.copy/>
                            </button>
                        </div>

                        <small id="key-expiry" style="display: block; margin-top: 5px; color: #64748b; font-size: 0.75rem;"></small>
                    </div>
                @endif
            </div>

            {{-- Thẻ khuyến mãi --}}
            <div class="promo-card">
                <span class="promo-tag">Unobvious Tips</span>
                <h4 class="promo-title">DEO BIET NEN LAM GI O DAY</h4>
                <p class="promo-meta">3 min read</p>
                <a href="#" class="promo-btn">
                    Read post <span class="arrow">→</span>
                </a>
            </div>
        </aside>

        <div class="content-container">
            <div class="content-header">
                <div>
                    <h3 class="content-title">Member Lists</h3>
                    <p class="content-subtitle">Manage and view club member informations</p>
                </div>
                <span class="content-badge-count">
                    {{ count($members) }} Members Active
                </span>
            </div>

            <div class="content-card">
                <div class="content-table-wrapper">
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Name & Email</th>
                                <th style="width: 12%;">Role</th>
                                <th style="width: 23%;">Instruments</th>
                                <th style="width: 15%;">Birthday</th>
                                <th style="width: 15%;">Joined Date</th>
                                <th style="width: 10%; text-align: right;">Actions</th>
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
                                        @php
                                            $role = strtolower($member->role ?? 'member');
                                            $badgeClass = 'content-badge-' . $role; // e.g., content-badge-president
                                        @endphp
                                        <span class="content-badge {{ $badgeClass }}" data-role="{{ $role }}">
                                            {{ ucfirst(str_replace('-', ' ', $role)) }}
                                        </span>
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
                                        <span class="content-date" data-birthday-raw="{{ $member->birthday ?? '' }}">
                                            {{ !empty($member->birthday) ? date('M d, Y', strtotime($member->birthday)) : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="content-date">
                                            {{ !empty($member->joined_in) ? date('M d, Y', strtotime($member->joined_in)) : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            {{-- Always show edit for own profile, otherwise show only if management --}}
                                            @if(Auth::id() === $member->_id || Auth::user()->isManagementTier())
                                                <button onclick="prepareAndOpenEditModal('{{ $member->_id }}')" class="btn-edit">                                                    <x-icons.edit/>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px 0; color: #64748b;">
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
        {{--1. ADD NEW MEMBER MODAL--}}
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
            <label class="form-label" for="add-birthday">Birthday</label>
            <input type="date" id="add-birthday" class="form-input">
        </div>

        <div class="form-group">
            <label class="form-label" for="add-role">Role Hierarchy</label>
            <select id="add-role" class="form-input" style="height: 42px;">
                <option value="member" selected>Member</option>
                <option value="manager">Manager</option>
                <option value="vice-president">Vice President</option>
                <option value="president">President</option>
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

         {{--2. EDIT EXISTING MEMBER MODAL--}}

    <x-modal id="editMemberModal" title="Edit Band Member" submitFn="submitEditForm(event)">
        <form id="editMemberModalForm" onsubmit="submitEditForm(event)">
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
            <label class="form-label" for="edit-birthday">Birthday</label>
            <input type="date" id="edit-birthday" name='birthday' class="form-input">
        </div>

        <div class="form-group">
            <label class="form-label" for="edit-role">Role</label>
            <select id="edit-role" class="form-input" style="height: 42px;">
                <option value="member">Member</option>
                <option value="manager">Manager</option>
                <option value="vice-president">Vice President</option>
                <option value="president">President</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="edit-instruments">Instruments</label>
            <input type="text" id="edit-instruments" class="form-input" placeholder="e.g. Vocal, Bass Guitar, Synth">
        </div>

        <x-slot name="footer">                                                  <x-icons.delete/>
            </button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </x-slot>
    </x-modal>
</x-navbar>
