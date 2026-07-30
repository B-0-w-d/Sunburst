{{-- Gọi component navbar tùy chỉnh với tiêu đề "Overview - Sunburst" --}}
<x-navbar title="Overview - Sunburst">
    <!-- Khung bọc giao diện trang tổng quan (overview), cài đặt hình nền (background) full màn hình bằng ảnh login-background.jpg kết hợp lớp phủ mờ tối (overlay) -->
    <div class="home-view-wrapper" style="position: relative; background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">

        <!-- Lớp phủ tối và làm mờ (backdrop overlay) giúp nội dung hiển thị nổi bật và rõ ràng hơn -->
        <div style="position: absolute; inset: 0; background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 1;"></div>

        <!-- Khối chứa nội dung bên trong trang overview, được đặt z-index cao hơn lớp phủ để hiển thị ở trên cùng -->
        <div class="content-container" style="position: relative; z-index: 2;">
            <!-- Header của phần overview -->
            <div class="content-header">
                <div>
                    <h1 class="content-title" style="color: #ffffff;">Foundation Overview</h1>
                    <p class="content-subtitle" style="color: #cbd5e1;">Tổng quan hệ thống</p>
                </div>
                <div class="content-badge-count">Live Status</div>
            </div>

            <!-- Khung thẻ nội dung (Card) chứa bảng hoặc các số liệu tổng quan -->
            <div class="content-card">
                <div style="padding: 24px;">
                    <!-- Đặt khung sườn nội dung overview của bạn ở đây -->
                    <p style="color: #475569; font-size: 14px;">Khung sườn overview đã sẵn sàng.</p>
                </div>
            </div>
        </div>
    </div>
{{-- Chú ý: Thẻ đóng component ở đây giữ nguyên theo code gốc --}}
</x-navbar>

{{-- Sử dụng @push('scripts') để đẩy đoạn script cấu hình modal vào stack scripts của layout chính --}}
@push('scripts')
<script>
    // Định nghĩa hàm toàn cục openModal để thêm class 'is-open' giúp hiển thị cửa sổ modal tương ứng theo ID
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('is-open');
    };

    // Định nghĩa hàm toàn cục closeModal để loại bỏ class 'is-open' giúp ẩn cửa sổ modal theo ID
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('is-open');
    };
</script>
@endpush
