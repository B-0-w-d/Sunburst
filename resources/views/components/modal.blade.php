@props([
    'id',        // ID HTML duy nhất dành cho modal
    'title',     // Tiêu đề hiển thị trên header của modal
    'maxWidth' => '460px', // Chiều rộng tối đa của khung modal, mặc định là 460px
    'submitFn' => ''       // Tên hàm JavaScript được gọi khi form trong modal được submit
])

{{-- Khung nền và hộp thoại modal tổng quát, nhận ID động --}}
<div class="modal" id="{{ $id }}">
    <div class="modal-window" style="max-width: {{ $maxWidth }};">
        <div class="modal-header">
            <h4 class="modal-title">{{ $title }}</h4>
            {{-- Nút đóng modal, gọi hàm closeModal với ID tương ứng --}}
            <button class="modal-close-btn" onclick="closeModal('{{ $id }}')">&times;</button>
        </div>

        {{-- Nếu có truyền hàm submit, tự động bọc nội dung bên trong bằng thẻ form --}}
        @if($submitFn)
            <form id="{{ $id }}Form" onsubmit="{{ $submitFn }}">
        @endif

            <div class="modal-body">
                {{-- Nội dung chính được truyền vào qua slot --}}
                {{ $slot }}
            </div>

            <div class="modal-footer">
                {{-- Khu vực chứa các nút chức năng ở chân modal, nếu không có sẽ để trống --}}
                {{ $footer ?? '' }}
            </div>

        @if($submitFn)
            </form>
        @endif
    </div>
</div>
