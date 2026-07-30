<!-- Khối thanh điều hướng chính (Navbar) chứa tiêu đề động của trang và các tùy chọn hành động nhanh -->
<nav class="navbar">
    <!-- Khu vực hiển thị thương hiệu hoặc tiêu đề trang, mặc định là 'Sunburst' nếu biến $title không được truyền vào -->
    <div class="navbar-brand">
        <h1>{{ $title ?? 'Sunburst' }}</h1>
    </div>

    <!-- Khu vực chứa các nút chức năng hoặc công cụ thao tác trên thanh navbar -->
    <div class="navbar-actions">
        <!-- Nút bấm kích hoạt mở modal chỉnh sửa thông tin người dùng với ID đích là editUserModal -->
        <button type="button"
                onclick="openModal('editUserModal')"
                class="dropdown-item"
                style="background: none; border: none; width: 100%; text-align: center; cursor: pointer;">
            Edit Profile
        </button>
    </div>
</nav>

<!-- Vùng hiển thị nội dung chính của các trang con được render động thông qua biến $slot của Layout Laravel -->
<main>
    {{ $slot }}
</main>

<!-- Khối khung cửa sổ Modal chỉnh sửa thông tin hồ sơ người dùng (định danh bằng ID chuẩn là editUserModal) -->
<div id="editUserModal" class="modal-backdrop">
    <div class="modal-window">
        <!-- Phần tiêu đề cố định ở phía trên cùng của modal, bao gồm tên tiêu đề và nút bấm đóng cửa sổ -->
        <div class="modal-header">
            <h2 class="modal-title">Edit Your Profile</h2>
            <!-- Nút chữ (×) gọi hàm đóng modal editUserModal -->
            <button type="button" class="modal-close-btn" onclick="closeModal('editUserModal')">&times;</button>
        </div>

        <!-- Thẻ Form bọc toàn bộ phần nội dung thân và chân modal để gửi yêu cầu cập nhật profile qua phương thức PUT -->
        <form action="{{ route('profile.update') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
            @csrf
            @method('PUT')

            <!-- Phần thân modal (modal-body) chứa các trường dữ liệu đầu vào của form -->
            <div class="modal-body">
                <!-- Khối kiểm tra và hiển thị danh sách thông báo lỗi chi tiết nếu dữ liệu nhập vào không hợp lệ -->
                @if ($errors->any())
                    <div style="color: red; margin-bottom: 10px; font-size: 13px;">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Nhóm trường nhập liệu Tên hiển thị (Display Name) của người dùng -->
                <div class="form-group">
                    <label class="form-label">DISPLAY NAME</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                </div>

                <!-- Nhóm trường nhập liệu Địa chỉ Email tài khoản -->
                <div class="form-group">
                    <label class="form-label">EMAIL ADDRESS</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                </div>

                <!-- Nhóm trường nhập liệu Ngày sinh (Birthday) -->
                <div class="form-group">
                    <label class="form-label">BIRTHDAY</label>
                    <input type="date" name="birthday" class="form-input" value="{{ old('birthday', auth()->user()->birthday ?? '') }}">
                </div>

                <!-- Nhóm trường nhập liệu Nhạc cụ sở trường (Instruments), tự động chuyển đổi mảng thành chuỗi phân tách bằng dấu phẩy -->
                <div class="form-group">
                    <label class="form-label">INSTRUMENTS</label>
                    @php
                        $userInstruments = auth()->user()->instrument ?? '';
                        $instrumentValue = is_array($userInstruments) ? implode(', ', $userInstruments) : $userInstruments;
                    @endphp
                    <input type="text" name="instrument" class="form-input" value="{{ $instrumentValue }}">
                </div>

                <!-- Đường kẻ phân cách trực quan giữa phần thông tin cá nhân và phần bảo mật mật khẩu -->
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

                <!-- Nhóm trường nhập liệu Mật khẩu mới (New Password) -->
                <div class="form-group">
                    <label class="form-label">NEW PASSWORD</label>
                    <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current">
                </div>

                <!-- Nhóm trường nhập liệu Xác nhận mật khẩu mới (Confirm Password) -->
                <div class="form-group">
                    <label class="form-label">CONFIRM PASSWORD</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
            </div>

            <!-- Phần chân form (modal-footer) chứa nút lưu thay đổi sử dụng class thiết kế sẵn btn-save -->
            <div class="modal-footer">
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Đoạn mã script tự động kích hoạt mở lại modal editUserModal khi trang tải xong nếu có lỗi validate từ server trả về -->
@if ($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof openModal === 'function') {
            openModal('editUserModal');
        }
    });
</script>
@endif
