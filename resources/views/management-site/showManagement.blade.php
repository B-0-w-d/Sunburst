{{-- Gọi component navbar tùy chỉnh với tiêu đề --}}
<x-navbar title="Quản Lý Show - Sunburst">
    <!-- Khung bọc giao diện trang tổng quan show -->
    <div class="home-view-wrapper" style="position: relative; background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">

        <!-- Lớp phủ tối và làm mờ (backdrop overlay) -->
        <div style="position: absolute; inset: 0; background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 1;"></div>

        <!-- Khối chứa nội dung bên trong -->
        <div class="content-container" style="position: relative; z-index: 2;">
            <div class="parent">

                <!-- Ô số 1: Widget Thống kê nhanh số lượng Show -->
                <div class="content-card div1" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff; border-radius: 16px;">
                    <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 10px 0;">Tổng Quan Show</h3>
                    <div style="text-align: center; margin: auto;">
                        <span id="totalShowsCount" style="font-size: 36px; font-weight: 800; color: #2563eb;">--</span>
                        <p style="font-size: 12px; color: #64748b; margin: 5px 0 0 0;">Tổng số show diễn</p>
                    </div>
                </div>

                <!-- Ô số 3: Thao tác nhanh (Nút mở Modal Tạo Show Mới) -->
                <div class="content-card div3" style="padding: 20px; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #ffffff; border-radius: 16px;">
                    <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Hành Động</h3>
                    <button type="button" onclick="openModal('createShowModal')" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; transition: background 0.2s;">
                        + Tạo Show Mới
                    </button>
                </div>

                <!-- Ô số 7: Danh sách các Show diễn (Grid hiển thị chính) -->
                <div class="content-card div7" style="padding: 16px; display: flex; flex-direction: column; height: 100%; box-sizing: border-box; background: #ffffff; border-radius: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; flex-shrink: 0;">
                        <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0;">Danh Sách Show Biểu Diễn</h3>
                        <span style="font-size: 11px; color: #64748b;" id="showCountBadge">Đang cập nhật...</span>
                    </div>

                    <!-- Khu vực chứa các thẻ Card của từng Show (được render bằng JS từ showManagement.js) -->
                    <div id="showsGridContainer" style="flex-grow: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; min-height: 0; padding-right: 4px;">
                        <p style="text-align: center; color: #94a3b8; font-size: 12px; margin: auto;">Đang tải danh sách show...</p>
                    </div>
                </div>

                <!-- Ô số 6: Bộ lọc / Tìm kiếm Show -->
                <div class="content-card div6" style="padding: 20px; display: flex; flex-direction: column; background: #ffffff; border-radius: 16px;">
                    <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Bộ Lọc Show</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="text" id="searchShowInput" placeholder="Tìm kiếm theo tên show..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
                        <select id="filterShowStatus" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: white;">
                            <option value="">Tất cả trạng thái</option>
                            <option value="upcoming">Sắp diễn ra</option>
                            <option value="completed">Đã hoàn thành</option>
                        </select>
                    </div>
                </div>

                <!-- Ô số 4: Thông tin bổ trợ / Ghi chú chung -->
                <div class="content-card div4" style="padding: 20px; display: flex; flex-direction: column; justify-content: center; background: #ffffff; border-radius: 16px;">
                    <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">Lưu Ý Setlist</h3>
                    <p style="color: #64748b; font-size: 12px; margin: 0; line-height: 1.4;">Hãy đảm bảo phân công bài hát và thành viên diễn chính xác trước mỗi lịch trình biểu diễn.</p>
                </div>

                <!-- Ô số 8: Show diễn tiếp theo sắp tới (Upcoming Show Widget) -->
                <div class="content-card div8" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; height: 100%; box-sizing: border-box; background: #ffffff; border-radius: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 14px; font-weight: 700; color: #1e293b;">Show Gần Nhất:</span>
                        <span id="nextShowBadge" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; background: #dbeafe; color: #1d4ed8;">Sắp tới</span>
                    </div>
                    <div id="nextShowWidgetContent" style="flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
                        <p style="font-size: 13px; color: #94a3b8; font-style: italic; margin: 0; text-align: center;">Đang tải thông tin show...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL TẠO SHOW MỚI -->
    <div id="createShowModal" class="custom-modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; width: 500px; max-width: 90%; border-radius: 12px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0;">Tạo Show Biểu Diễn Mới</h3>
                <button type="button" onclick="closeModal('createShowModal')" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">&times;</button>
            </div>

            <form id="createShowForm">
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Tên Show</label>
                    <input type="text" id="showTitleInput" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Thời gian</label>
                    <input type="date" id="showDateInput" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Địa điểm</label>
                    <input type="text" id="showLocationInput" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Chọn Bài Hát Cho Setlist</label>
                    <div id="songsCheckboxList" style="border: 1px solid #cbd5e1; border-radius: 6px; max-height: 150px; overflow-y: auto; padding: 10px; font-size: 12px;">
                        <span style="color: #94a3b8;">Đang tải danh sách bài hát...</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" onclick="closeModal('createShowModal')" style="padding: 8px 16px; background: #e2e8f0; border: none; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">Hủy</button>
                    <button type="button" id="saveNewShowBtn" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">Lưu Show</button>
                </div>
            </form>
        </div>
    </div>
</x-navbar>

{{-- Đẩy script xử lý Modal và file JS quản lý show vào stack --}}
@push('scripts')
<script>
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'flex';
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    };
</script>
<!-- Tải file logic JS quản lý show của bạn -->
<script type="module" src="{{ asset('resources/js/showManagement.js') }}"></script>
@endpush
