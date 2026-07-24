<!-- Navbar chính của bạn -->
<nav class="navbar">
    <div class="navbar-brand">
        <h1>{{ $title ?? 'Sunburst' }}</h1>
    </div>

    <div class="navbar-actions">
        <!-- Nút gọi mở modal ĐÃ ĐỔI ĐÚNG ID -->
        <button type="button"
                onclick="openModal('editUserModal')"
                class="dropdown-item"
                style="background: none; border: none; width: 100%; text-align: center; cursor: pointer;">
            Edit Profile
        </button>
    </div>
</nav>

<!-- Nội dung các trang khác sẽ được render qua $slot -->
<main>
    {{ $slot }}
</main>

<!-- Modal với ID chuẩn là editUserModal -->
<!-- Modal với ID chuẩn là editUserModal -->
<div id="editUserModal" class="modal-backdrop">
    <div class="modal-window">
        <!-- Phần tiêu đề cố định ở phía trên -->
        <div class="modal-header">
            <h2 class="modal-title">Edit Your Profile</h2>
            <button type="button" class="modal-close-btn" onclick="closeModal('editUserModal')">&times;</button>
        </div>

        <!-- Thẻ Form bọc toàn bộ phần nội dung thân và chân modal -->
        <form action="{{ route('profile.update') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <!-- Hiển thị danh sách thông báo lỗi nếu có dữ liệu đầu vào không hợp lệ -->
                @if ($errors->any())
                    <div style="color: red; margin-bottom: 10px; font-size: 13px;">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">DISPLAY NAME</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">EMAIL ADDRESS</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">BIRTHDAY</label>
                    <input type="date" name="birthday" class="form-input" value="{{ old('birthday', auth()->user()->birthday ?? '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">INSTRUMENTS</label>
                    @php
                        $userInstruments = auth()->user()->instrument ?? '';
                        $instrumentValue = is_array($userInstruments) ? implode(', ', $userInstruments) : $userInstruments;
                    @endphp
                    <input type="text" name="instrument" class="form-input" value="{{ $instrumentValue }}">
                </div>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

                <div class="form-group">
                    <label class="form-label">NEW PASSWORD</label>
                    <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current">
                </div>

                <div class="form-group">
                    <label class="form-label">CONFIRM PASSWORD</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
            </div>

            <!-- Phần chân form chứa nút lưu thay đổi sử dụng class btn-save có sẵn -->
            <div class="modal-footer">
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Script tự động mở lại modal nếu có lỗi validate -->
@if ($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof openModal === 'function') {
            openModal('editUserModal');
        }
    });
</script>
@endif
