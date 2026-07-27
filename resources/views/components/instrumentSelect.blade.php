@props([
    'id' => 'instrument-selector',
    'available' => ['Media' ,'Designer','Clean Vocal', 'Scream Vocal', 'Rap', 'Guitar', 'Bass', 'Drums', 'Keyboard', 'Piano', 'Violin', 'Saxophone'],
    'selected' => []
])

@php
    $selectedList = is_array($selected) ? $selected : [];
    // Lọc ra các nhạc cụ chưa được chọn cho vùng Available
    $availableList = array_diff($available, $selectedList);
@endphp

<div class="instruments-component" id="{{ $id }}" data-component="instrument-selector">

    <!-- KHU VỰC NHẠC CỤ CÓ SẴN -->
    <div class="instrument-group">
        <span class="instrument-title text-available">Gợi ý nhạc cụ:</span>
        <div class="instrument-zone available-zone">
            @foreach($availableList as $item)
                <div class="instrument-chip" draggable="true" data-value="{{ $item }}">
                    <span class="chip-label">{{ $item }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- KHU VỰC NHẠC CỤ ĐÃ CHỌN -->
    <div class="instrument-group">
        <span class="instrument-title">Nhạc cụ đã chọn:</span>
        <div class="instrument-zone selected-zone">
            @foreach($selectedList as $item)
                <div class="instrument-chip" draggable="true" data-value="{{ $item }}">
                    <span class="chip-label">{{ $item }}</span>
                    <button type="button" class="btn-remove-chip">&times;</button>
                </div>
            @endforeach
        </div>
    </div>
    <!-- KHU VỰC THÊM NHẠC CỤ MỚI -->
    <div class="instrument-add-box">
        <input
            type="text"
            class="instrument-custom-input"
            placeholder="Nhập sở trường khác (Flute, Ukulele)..."
            maxlength="30"
        />
        <button type="button" class="btn-add-instrument" title="Thêm nhạc cụ">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>
    </div>
</div>
