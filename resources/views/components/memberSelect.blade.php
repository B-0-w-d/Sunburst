{{-- Khai báo các thuộc tính (props) đầu vào cho Blade Component chọn thành viên kèm theo giá trị mặc định --}}
@props([
    'id' => 'member-selector',
    'members' => [], // Mảng các object/array: [['id' => 1, 'name' => 'Nguyễn Văn A'], ...]
    'selected' => []  // Mảng chứa các ID thành viên đã được chọn sẵn từ trước
])

@php
    // Đảm bảo biến $selectedIds luôn là một mảng để tránh lỗi xử lý dữ liệu
    $selectedIds = is_array($selected) ? $selected : [];

    // Phân tách toàn bộ danh sách thành viên thành hai nhóm: chưa chọn (available) và đã chọn (selected) dựa theo mảng $selectedIds
    $selectedList = collect($members)->filter(fn($m) => in_array($m['id'], $selectedIds))->values()->all();
    $availableList = collect($members)->filter(fn($m) => !in_array($m['id'], $selectedIds))->values()->all();
@endphp

{{-- Khối component chọn thành viên --}}
<div class="drag-drop-component
 member-selector-component" id="{{ $id }}" data-component="member-selector">

    <!-- KHU VỰC THÀNH VIÊN CÓ SẴN -->
    <div class="drag-drop-group">
        <span class="drag-drop-title text-available">Thành viên câu lạc bộ:</span>
        <div class="drag-zone available-zone" id="availableMembersZone" ondragover="event.preventDefault()" ondrop="window.handleMemberDrop(event, 'available')">
            @foreach($availableList as $member)
                {{-- Bổ sung data-drags để JS đọc được nhạc cụ --}}
                <div class="drag-chip member-chip" draggable="true"
                     data-id="{{ $member['id'] }}"
                     data-drags="{{ json_encode($member['drag'] ?? ($member['drags'] ?? [])) }}"
                     ondragstart="window.handleMemberDragStart(event)">
                    <span class="chip-label">{{ $member['name'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- KHU VỰC THÀNH VIÊN THAM GIA -->
    <div class="drag-drop-group">
        <span class="drag-drop-title">Thành viên tham gia (Mục tiêu):</span>
        <div class="drag-zone selected-zone" id="selectedMembersZone" ondragover="event.preventDefault()" ondrop="window.handleMemberDrop(event, 'selected')">
            @foreach($selectedList as $member)
                {{-- Bổ sung data-drags tương tự cho các thành viên đã chọn --}}
                <div class="drag-chip member-chip" draggable="true"
                     data-id="{{ $member['id'] }}"
                     data-drags="{{ json_encode($member['drag'] ?? ($member['drags'] ?? [])) }}"
                     ondragstart="window.handleMemberDragStart(event)">
                    <span class="chip-label">{{ $member['name'] }}</span>
                    <button type="button" class="btn-remove-chip" onclick="window.removeMemberChip(this)">×</button>
                </div>
            @endforeach
        </div>
    </div>

    <input type="hidden" name="targetMemberIds" id="targetMemberIds" value="{{ implode(',', $selectedIds) }}">
</div>
