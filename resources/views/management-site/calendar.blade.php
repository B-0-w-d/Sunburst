{{-- Nhúng thành phần giao diện khung điều hướng chung (Navbar Layout) --}}
<x-navbar>
    <div class="calendar-container">
        <!-- ===================================================================== -->
        <!-- PHẦN ĐẦU TRANG & TIÊU ĐỀ CHÍNH                                        -->
        <!-- ===================================================================== -->
        <div class="calendar-header">
            <div>
                <h2>Quản Lý Lịch & Khảo Sát CLB</h2>
                <p class="subtitle">Hệ thống lịch trình và khảo sát thời gian rảnh trực tuyến</p>
            </div>
            <!-- Chỉ hiển thị nút tạo lịch/khảo sát nếu tài khoản thuộc cấp quản lý -->
            @if(auth()->user()->isManagementTier())
                <button id="openCreateModalBtn" class="btn btn-primary">+ Tạo Lịch / Khảo Sát</button>
            @endif
        </div>

        <!-- ===================================================================== -->
        <!-- KHU VỰC HIỂN THỊ DANH SÁCH (CHIA THÀNH 2 SECTION DỌC)                  -->
        <!-- ===================================================================== -->
        <div class="sections-container" style="display: flex; flex-direction: column; gap: 30px; margin-top: 20px;">

            <!-- 1. KHU VỰC LỊCH ĐÃ CHỐT (Ở TRÊN) -->
            <div class="section-block">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid #007bff; padding-bottom: 5px; display: inline-block;">📅 Lịch Đã Chốt</h3>
                <div class="event-list-wrapper">
                    <div id="confirmedEventList" class="event-grid">
                        <!-- Danh sách sự kiện CONFIRMED sẽ được JavaScript render vào đây -->
                    </div>
                </div>
            </div>

            <!-- 2. KHU VỰC KHẢO SÁT ĐANG MỞ (Ở DƯỚI) -->
            <div class="section-block">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid #28a745; padding-bottom: 5px; display: inline-block;">📊 Khảo Sát Đang Mở</h3>
                <div class="event-list-wrapper">
                    <div id="pollEventList" class="event-grid">
                        <!-- Danh sách khảo sát POLL sẽ được JavaScript render vào đây -->
                    </div>
                </div>
            </div>

        </div>

        <!-- ===================================================================== -->
        <!-- MODAL: TẠO SỰ KIỆN HOẶC KHẢO SÁT MỚI                                   -->
        <!-- ===================================================================== -->
        <div id="createEventModal" class="custom-modal">
            <div class="modal-content">
                <span class="close-btn" data-modal="createEventModal">&times;</span>
                <h3>Tạo Sự Kiện Hoặc Khảo Sát Mới</h3>
                <form id="createEventForm">
                    <div class="form-group">
                        <label>Tiêu đề sự kiện</label>
                        <input type="text" id="eventTitle" required placeholder="Ví dụ: Khảo sát lịch tập band...">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phân loại (Type)</label>
                            <select id="eventType">
                                <option value="PRACTICE">PRACTICE (Lịch tập)</option>
                                <option value="MEETING">MEETING (Lịch họp)</option>
                                <option value="SHOW">SHOW (Show diễn)</option>
                                <option value="EVENT">EVENT (Nội bộ)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái khởi tạo</label>
                            <select id="eventStatus">
                                <option value="POLL">POLL (Khảo sát thời gian)</option>
                                <option value="CONFIRMED">CONFIRMED (Chốt lịch luôn)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cấu hình thời gian dành riêng cho dạng Khảo Sát (POLL) -->
                    <div id="pollConfigSection" class="config-section">
                        <h4>Cấu hình thời gian khảo sát</h4>
                        <div class="form-row">
                            <div class="form-group"><label>Ngày bắt đầu</label><input type="date" id="pollStartDate"></div>
                            <div class="form-group"><label>Ngày kết thúc</label><input type="date" id="pollEndDate"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Giờ mở đầu ngày</label><input type="time" id="dailyStartTime" value="18:00"></div>
                            <div class="form-group"><label>Giờ kết thúc ngày</label><input type="time" id="dailyEndTime" value="22:00"></div>
                        </div>
                    </div>

                    <!-- Cấu hình thời gian dành riêng cho dạng Lịch Cố Định (CONFIRMED) -->
                    <div id="confirmedConfigSection" class="config-section" style="display: none;">
                        <h4>Thời gian diễn ra sự kiện</h4>
                        <div class="form-row">
                            <div class="form-group"><label>Bắt đầu</label><input type="datetime-local" id="startTime"></div>
                            <div class="form-group"><label>Kết thúc</label><input type="datetime-local" id="endTime"></div>
                        </div>
                    </div>

                    <!-- Component chọn thành viên tham gia -->
                    <div class="form-group">
                        <label>Thành viên tham gia (Kéo thả hoặc bấm vào tên để thêm)</label>
                        <x-memberSelect
                            id="eventMemberSelector"
                            :members="$allMembers"
                            :selected="[]"
                        />
                    </div>

                    <button type="submit" class="btn btn-success w-100">Xác Nhận Tạo</button>
                </form>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- MODAL: ĐIỀN LỊCH RẢNH                                                 -->
        <!-- ===================================================================== -->
        <div id="pollMatrixModal" class="custom-modal">
            <div class="modal-content modal-large">
                <span class="close-btn" data-modal="pollMatrixModal">&times;</span>
                <h3 id="pollMatrixTitle">Điền Lịch Rảnh</h3>
                <p class="subtitle">Kéo chuột bôi đen các ô thời gian bạn rảnh, sau đó bấm Lưu.</p>

                <div class="matrix-scroll-container">
                    <table id="w2mMatrixTable" class="w2m-table">
                        <!-- Bảng ma trận tương tác điền lịch sẽ được render động tại đây -->
                    </table>
                </div>

                <div class="modal-actions">
                    <button id="saveAvailabilityBtn" class="btn btn-success">Lưu Lịch Rảnh</button>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- MODAL: BÁO CÁO TỔNG HỢP & CHỐT LỊCH                                     -->
        <!-- ===================================================================== -->
        <div id="reportModal" class="custom-modal">
            <div class="modal-content modal-large">
                <span class="close-btn" data-modal="reportModal">&times;</span>
                <h3 id="reportTitle">Báo Cáo Tổng Hợp Khảo Sát</h3>
                <div class="report-stats">
                    <span id="statTargetCount">Tổng mục tiêu: 0</span>
                    <span id="statSubmittedCount">Đã phản hồi: 0</span>
                </div>

                <div class="matrix-scroll-container">
                    <table id="reportMatrixTable" class="w2m-table">
                        <!-- Bảng biểu đồ nhiệt (Heatmap) báo cáo sẽ được render động tại đây -->
                    </table>
                </div>

                <div class="modal-actions">
                    <button id="confirmPollBtn" class="btn btn-primary">Chốt Lịch Này</button>
                </div>
            </div>
        </div>
    </div>
</x-navbar>

{{-- Khai báo Styles và Scripts chuyển ra ngoài thẻ Component để Stack đẩy đúng vị trí --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('app.css') }}">
@endpush

@push('scripts')
    @vite(['resources/js/calendar.js'])
@endpush
