<x-navbar>
    <div class="calendar-container">
        <!-- Header Section -->
        <div class="calendar-header">
            <div>
                <h2>Quản Lý Lịch & Khảo Sát CLB</h2>
                <p class="subtitle">Hệ thống lịch trình và khảo sát thời gian rảnh When2meet</p>
            </div>
            @if(auth()->user()->isManagementTier())
                <button id="openCreateModalBtn" class="btn btn-primary">+ Tạo Lịch / Khảo Sát</button>
            @endif
        </div>

        <!-- Tabs chuyển đổi qua lại -->
        <div class="calendar-tabs">
            <button class="tab-btn active" data-tab="confirmed">Lịch Đã Chốt</button>
            <button class="tab-btn" data-tab="poll">Khảo Sát Đang Mở (When2meet)</button>
        </div>

        <!-- Danh sách hiển thị -->
        <div class="event-list-wrapper">
            <div id="eventList" class="event-grid">
                <!-- Dữ liệu được render động bằng JavaScript -->
            </div>
        </div>

        <!-- MODAL 1: Admin tạo lịch / khảo sát -->
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
                                <option value="POLL">POLL (Khảo sát When2meet)</option>
                                <option value="CONFIRMED">CONFIRMED (Chốt lịch luôn)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Khu vực cấu hình riêng cho POLL -->
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

                    <!-- Khu vực cấu hình riêng cho CONFIRMED trực tiếp -->
                    <div id="confirmedConfigSection" class="config-section" style="display: none;">
                        <h4>Thời gian diễn ra sự kiện</h4>
                        <div class="form-row">
                            <div class="form-group"><label>Bắt đầu</label><input type="datetime-local" id="startTime"></div>
                            <div class="form-group"><label>Kết thúc</label><input type="datetime-local" id="endTime"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Danh sách ID Thành viên tham gia (phân cách bằng dấu phẩy)</label>
                        <input type="text" id="targetMemberIds" placeholder="id_1, id_2, id_3...">
                    </div>

                    <button type="submit" class="btn btn-success w-100">Xác Nhận Tạo</button>
                </form>
            </div>
        </div>

        <!-- MODAL 2: Giao diện Ma trận When2meet cho Thành viên -->
        <div id="when2meetModal" class="custom-modal">
            <div class="modal-content modal-large">
                <span class="close-btn" data-modal="when2meetModal">&times;</span>
                <h3 id="w2mTitle">Điền Lịch Rảnh</h3>
                <p class="subtitle">Kéo chuột bôi đen các ô thời gian bạn rảnh, sau đó bấm Lưu.</p>

                <div class="matrix-scroll-container">
                    <table id="w2mMatrixTable" class="w2m-table">
                        <!-- Sinh ma trận bằng JS -->
                    </table>
                </div>

                <div class="modal-actions">
                    <button id="saveAvailabilityBtn" class="btn btn-success">Lưu Lịch Rảnh</button>
                </div>
            </div>
        </div>

        <!-- MODAL 3: Admin xem báo cáo & Chốt lịch -->
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
                        <!-- Heatmap report -->
                    </table>
                </div>

                <div class="form-group mt-3">
                    <label>Chốt khung giờ từ báo cáo:</label>
                    <div class="form-row">
                        <input type="datetime-local" id="confirmStartTime">
                        <input type="datetime-local" id="confirmEndTime">
                    </div>
                </div>

                <div class="modal-actions">
                    <button id="confirmPollBtn" class="btn btn-primary">Chốt Lịch Này</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Đính kèm file CSS và JS theo cấu trúc thư mục -->
        @push('styles')
            <link rel="stylesheet" href="{{ asset('app.css') }}">
        @endpush

        @push('scripts')
                @vite(['resources/js/calendar.js'])
            @endpush
</x-navbar>
