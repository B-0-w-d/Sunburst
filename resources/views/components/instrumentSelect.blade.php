{{-- Định nghĩa các thuộc tính nhận vào (props) cho Blade Component với giá trị mặc định tương ứng --}}
@props([
    'id' => 'instrument-selector',
    'available' => ['Media' ,'Designer','Clean Vocal', 'Scream Vocal', 'Rap', 'Guitar', 'Bass', 'Drums', 'Keyboard', 'Piano', 'Violin', 'Saxophone'],
    'selected' => []
])

@php
    // Chuẩn hóa biến selected thành mảng đảm bảo an toàn dữ liệu
    $selectedList = is_array($selected) ? $selected : [];
    // Lọc ra các nhạc cụ chưa được chọn để hiển thị ở vùng Available (loại bỏ các phần tử trùng với selectedList)
    $availableList = array_diff($available, $selectedList);
@endphp

{{-- Khối bao bọc toàn bộ component lựa chọn nhạc cụ, chứa định danh (id) và thuộc tính data-component --}}
<div class="drag-drop-component
" id="{{ $id }}" data-component="instrument-selector">

    <!-- KHU VỰC NHẠC CỤ CÓ SẴN (hiển thị danh sách gợi ý chưa được chọn để người dùng kéo thả) -->
    <div class="drag-drop-group">
        <span class="text-subtitle text-available">Gợi ý nhạc cụ:</span>
        <div class="drag-zone available-zone">
            @foreach($availableList as $item)
                {{-- Mỗi thẻ nhạc cụ (chip) cho phép kéo thả (draggable="true") kèm theo giá trị lưu trữ trong data-value --}}
                <div class="drag-chip" draggable="true" data-value="{{ $item }}">
                    <span class="chip-label">{{ $item }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- KHU VỰC NHẠC CỤ ĐÃ CHỌN (hiển thị danh sách các mục mà người dùng đã thêm/chọn) -->
    <div class="drag-drop-group">
        <span class="text-subtitle text-available">Nhạc cụ đã chọn:</span>
        <div class="drag-zone selected-zone">
            @foreach($selectedList as $item)
                {{-- Thẻ chip đã chọn, có kèm theo nút bấm (x) để gỡ bỏ nhạc cụ khỏi danh sách --}}
                <div class="drag-chip" draggable="true" data-value="{{ $item }}">
                    <span class="chip-label">{{ $item }}</span>
                    <button type="button" class="btn-remove-chip">&times;</button>
                </div>
            @endforeach
        </div>
    </div>
    <!-- KHU VỰC THÊM NHẠC CỤ MỚI (cho phép nhập văn bản tùy chỉnh và nút bấm xác nhận thêm) -->
    <div class="drop-add-box">
        <!-- Ô nhập liệu để người dùng tự gõ thêm sở trường/nhạc cụ ngoài danh sách gợi ý -->
        <input
            type="text"
            class="drop-custom-input"
            placeholder="Nhập sở trường khác (Flute, Ukulele)..."
            maxlength="30"
        />
        <!-- Nút bấm chứa biểu tượng dấu cộng (+) để kích hoạt sự kiện thêm nhạc cụ mới -->
        <button type="button" class="btn-add" title="Thêm nhạc cụ">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>
    </div>
</div>
