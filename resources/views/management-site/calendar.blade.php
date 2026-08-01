{{-- Nhúng thành phần giao diện khung điều hướng chung (Navbar Layout) --}}
<x-navbar>
    <div style="display: flex; width: 100%; min-height: calc(100vh - 70px); align-items: stretch; margin: 0; padding: 0;">

        <!-- ===================================================================== -->
        <!-- 1. SIDEBAR BÊN TRÁI: ĐIỀU HƯỚNG, TIÊU ĐỀ, NÚT TẠO VÀ LỊCH NHỎ -->
        <!-- ===================================================================== -->
        <aside style="width: 280px; background-color: #ffffff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 20px; box-sizing: border-box; flex-shrink: 0; overflow-y: auto;">
            <!-- Lịch tháng nhỏ điều hướng -->
            <div id="miniCalendarWidget" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h4 id="miniCalendarMonthYear" style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0;">Tháng 8, 2026</h4>
                    <div style="display: flex; gap: 4px;">
                        <button type="button" id="miniPrevBtn" style="background: none; border: none; cursor: pointer; font-size: 14px; color: #64748b; font-weight: bold;">‹</button>
                        <button type="button" id="miniNextBtn" style="background: none; border: none; cursor: pointer; font-size: 14px; color: #64748b; font-weight: bold;">›</button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-size: 11px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">
                    <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                </div>
                <div id="miniCalendarGrid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; font-size: 12px;">
                    <!-- Render bằng JavaScript -->
                </div>
            </div>

            <!-- Khu vực Tiêu đề & Nút Tạo Lịch/Khảo Sát -->
            <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                <h2 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">Quản Lý Lịch & Khảo Sát CLB</h2>
                <p class="subtitle" style="color: #64748b; font-size: 12px; margin: 0 0 12px 0; line-height: 1.4;">Hệ thống lịch trình và khảo sát thời gian rảnh trực tuyến</p>

                @if(auth()->user()->isManagementTier())
                    <button id="openCreateModalBtn" class="btn btn-primary" style="width: 100%; font-size: 13px; padding: 8px 12px; justify-content: center;">+ Tạo Lịch / Khảo Sát</button>
                @endif
            </div>

            <!-- Menu liên kết (Upcoming Shows / Danh mục) -->
            <div style="margin-bottom: 25px;">
                <div style="margin-bottom: 12px;">
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 700;">Upcoming Shows</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; color: #475569; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6;"></span> Campaigns
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; color: #1e293b; text-decoration: none; font-size: 14px; font-weight: 600; background-color: #f8fafc; box-shadow: inset 3px 0 0 #dc2626;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #ef4444;"></span> Publications
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; color: #475569; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span> Development
                    </a>
                </div>
            </div>



            <!-- Khối thông tin bổ sung / Tip ở chân sidebar -->
            <div class="card promo-card-sidebar" style="margin-top: auto; background: #fdf2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 15px; box-sizing: border-box;">
                <span class="promo-tag" style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #dc2626; background: #fee2e2; padding: 2px 6px; border-radius: 4px;">Unobvious Tips</span>
                <h4 class="promo-title" style="font-size: 13px; font-weight: 700; color: #1e293b; margin: 8px 0 4px 0; line-height: 1.4;">DEO BIET NEN LAM GI O DAY</h4>
                <p class="promo-meta" style="font-size: 11px; color: #64748b; margin-bottom: 10px;">3 min read</p>
                <a href="#" class="promo-btn" style="font-size: 12px; font-weight: 600; color: #dc2626; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    Read post <span class="arrow">→</span>
                </a>
            </div>
        </aside>

        <!-- ===================================================================== -->
        <!-- 2. KHUNG NỘI DUNG CHÍNH BÊN PHẢI: LỊCH ĐÃ CHỐT & KHẢO SÁT ĐANG MỞ -->
        <!-- ===================================================================== -->
        <main class="calendar-container" style="flex: 1; padding: 30px; background-color: #f8fafc; box-sizing: border-box; overflow-y: auto;">

            <!-- Phần 2.1: Khối Lịch Đã Chốt (Nền trắng, bo góc, đổ bóng nhẹ) -->
            <div style="background: #ffffff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 40px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 id="currentWeekTitle" style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #10b981;"></span> Lịch Đã Chốt (Theo Tuần)
                    </h3>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" id="weekTodayBtn" class="btn" style="padding: 6px 12px; font-size: 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; cursor: pointer;">Hôm nay</button>
                        <div style="display: inline-flex; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #fff;">
                            <button type="button" id="weekPrevBtn" style="padding: 6px 12px; background: #fff; border: none; cursor: pointer; font-weight: bold; font-size: 14px; line-height: 1;">‹</button>
                            <button type="button" id="weekNextBtn" style="padding: 6px 12px; background: #fff; border: none; border-left: 1px solid #cbd5e1; cursor: pointer; font-weight: bold; font-size: 14px; line-height: 1;">›</button>
                        </div>
                    </div>
                </div>

                <!-- Lưới lịch tuần (Time-Grid View) -->
                <div style="overflow-x: auto;">
                    <div id="weeklyCalendarGrid" style="min-width: 200px; display: grid; grid-template-columns: 20px repeat(7, 1fr); border-top: 1px solid #e2e8f0;">
                        <!-- Render bằng JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Phần 2.2: Khu vực danh sách Khảo Sát Đang Mở -->
            <div style="margin-bottom: 15px; border-top: 1px solid #e2e8f0; padding-top: 25px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 15px 0; display: flex; align-items: center; gap: 8px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #3b82f6;"></span> Khảo Sát Đang Mở
                </h3>
            </div>
            <div class="event-grid" id="pollEventList"></div>

            <!-- ===================================================================== -->
            <!-- 3. CÁC MODAL (HỘP THOẠI TƯƠNG TÁC) -->
            <!-- ===================================================================== -->

            <!-- Modal: Tạo Sự Kiện Hoặc Khảo Sát Mới -->
            <div id="createEventModal" class="custom-modal">
                <div class="modal-content modal-horizontal">
                    <span class="close-btn" data-modal="createEventModal">&times;</span>
                    <h3>Tạo Sự Kiện Hoặc Khảo Sát Mới</h3>

                    <form id="createEventForm">
                        <div class="modal-grid-layout">
                            <!-- Cột trái cấu hình sự kiện -->
                            <div class="modal-col-left">
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

                                <div id="pollConfigSection" class="config-section">
                                    <h4>Cấu hình thời gian khảo sát</h4>
                                    <div class="form-row">
                                        <div class="form-group"><label>Ngày bắt đầu</label><input type="date" id="pollStartDate"></div>
                                        <div class="form-group"><label>Ngày kết thúc</label><input type="date" id="pollEndDate"></div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group"><label>Giờ mở đầu ngày</label><input type="time" id="dailyStartTime" value="06:00"></div>
                                        <div class="form-group"><label>Giờ kết thúc ngày</label><input type="time" id="dailyEndTime" value="23:59"></div>
                                    </div>
                                </div>

                                <div id="confirmedConfigSection" class="config-section" style="display: none;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <h4 style="margin: 0;">Thời gian diễn ra sự kiện</h4>
                                        <label style="font-size: 13px; font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                            <input type="checkbox" id="allDayEventCheckbox" style="cursor: pointer;"> Cả ngày
                                        </label>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group"><label>Bắt đầu</label><input type="datetime-local" id="startTime"></div>
                                        <div class="form-group"><label>Kết thúc</label><input type="datetime-local" id="endTime"></div>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 15px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Thời điểm nhắc nhở trước:</label>
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                        <label class="reminder-chip-label"><input type="checkbox" name="remindMinutes" value="30" checked><span>30 phút</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="remindMinutes" value="60"><span>1 tiếng</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="remindMinutes" value="360"><span>6 tiếng</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="remindMinutes" value="720"><span>12 tiếng</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="remindMinutes" value="1440"><span>1 ngày</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="remindMinutes" value="2880"><span>2 ngày</span></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Cột phải chọn thành viên tham gia -->
                            <div class="modal-col-right">
                                <div class="form-group" style="height: 100%; display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 8px;">Thành viên tham gia</label>
                                    <div style="flex: 1; min-height: 300px;">
                                        <x-memberSelect id="eventMemberSelector" :members="$allMembers" :selected="[]" />
                                    </div>
                                    <button type="button" id="selectAllMembersBtn" class="btn" style="margin-top: 10px;">Chọn tất cả</button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100" style="margin-top: 20px;">Xác Nhận Tạo</button>
                    </form>
                </div>
            </div>

            <!-- Modal: Điền Lịch Rảnh Cá Nhân -->
            <div id="pollMatrixModal" class="custom-modal">
                <div class="modal-content modal-large">
                    <span class="close-btn" data-modal="pollMatrixModal">&times;</span>
                    <h3 id="pollMatrixTitle">Điền Lịch Rảnh</h3>
                    <p class="subtitle">Kéo chuột bôi đen các ô thời gian bạn rảnh, sau đó bấm Lưu.</p>

                    <div class="matrix-scroll-container">
                        <table id="w2mMatrixTable" class="w2m-table"></table>
                    </div>

                    <div class="modal-actions">
                        <button id="saveAvailabilityBtn" class="btn btn-success">Lưu Lịch Rảnh</button>
                    </div>
                </div>
            </div>

            <!-- Modal: Báo Cáo Tổng Hợp & Chốt Lịch -->
            <div id="reportModal" class="custom-modal">
                <div class="modal-content modal-large">
                    <span class="close-btn" data-modal="reportModal">&times;</span>
                    <h3 id="reportTitle">Báo Cáo Tổng Hợp Khảo Sát</h3>
                    <div class="report-stats">
                        <span id="statTargetCount">Tổng mục tiêu: 0</span>
                        <span id="statSubmittedCount">Đã phản hồi: 0</span>
                    </div>

                    <div class="matrix-scroll-container">
                        <table id="reportMatrixTable" class="w2m-table"></table>
                    </div>

                    <div class="modal-actions">
                        <button id="confirmPollBtn" class="btn btn-primary">Chốt Lịch Này</button>
                    </div>
                </div>
            </div>

        </main>
    </div>
</x-navbar>

{{-- Khai báo CSS và JS đính kèm --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('app.css') }}">
@endpush

@push('scripts')
    @vite(['resources/js/calendar.js'])
@endpush
