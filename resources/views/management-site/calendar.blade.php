{{-- Nhúng thành phần giao diện khung điều hướng chung (Navbar Layout) --}}
<x-navbar>
    <div class="app-layout">

        <!-- ===================================================================== -->
        <!-- 1. SIDEBAR BÊN TRÁI: ĐIỀU HƯỚNG, TIÊU ĐỀ, NÚT TẠO VÀ LỊCH NHỎ -->
        <!-- ===================================================================== -->
        <aside class="app-sidebar">

            <!-- Lịch tháng nhỏ điều hướng -->
            <div id="miniCalendarWidget" class="mini-calendar-widget">
                <div class="mini-calendar-header">
                    <h4 id="miniCalendarMonthYear" class="mini-calendar-title">Tháng 8, 2026</h4>
                    <div class="mini-calendar-nav-btns">
                        <button type="button" id="miniPrevBtn" class="mini-cal-btn">‹</button>
                        <button type="button" id="miniNextBtn" class="mini-cal-btn">›</button>
                    </div>
                </div>
                <div class="mini-calendar-days">
                    <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                </div>
                <div id="miniCalendarGrid" class="mini-calendar-grid">
                    <!-- Render bằng JavaScript -->
                </div>
            </div>

            <!-- Khu vực Tiêu đề & Nút Tạo Lịch/Khảo Sát -->
            <div class="sidebar-header">
                <h2 class="sidebar-title">Quản Lý Lịch & Khảo Sát CLB</h2>
                <p class="sidebar-subtitle">Hệ thống lịch trình và khảo sát thời gian rảnh trực tuyến</p>

                @if(auth()->user()->isManagementTier())
                    <button id="openCreateModalBtn" class="btn btn-primary" style="width: 100%; font-size: 13px; padding: 8px 12px; justify-content: center;">+ Tạo Lịch / Khảo Sát</button>
                @endif
            </div>

            <!-- Danh sách sự kiện sắp tới (Upcoming Events) -->
            <div class="sidebar-nav-section">
                <div class="sidebar-nav-label">Sự Kiện Sắp Tới</div>
                <div id="upcomingEventsList" class="sidebar-nav-list" style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Render danh sách sự kiện sắp tới bằng JavaScript -->
                </div>
            </div>

            <!-- Khối thông tin bổ sung / Tip ở chân sidebar -->
            <div class="card promo-card-sidebar">
                <span class="promo-tag">Unobvious Tips</span>
                <h4 class="promo-title">DEO BIET NEN LAM GI O DAY</h4>
                <p class="promo-meta">3 min read</p>
                <a href="#" class="promo-btn">
                    Read post <span class="arrow">→</span>
                </a>
            </div>
        </aside>

        <!-- ===================================================================== -->
        <!-- 2. KHUNG NỘI DUNG CHÍNH BÊN PHẢI (CHIA 2 CỘT: LỊCH & KHẢO SÁT) -->
        <!-- ===================================================================== -->
        <main class="main-content-area">

            <div class="content-grid-wrapper">

                <!-- CỘT TRÁI: Khối Lịch Đã Chốt -->
                <div class="left-column-content">
                    <div class="confirmed-calendar-card">
                        <div class="confirmed-calendar-header">
                            <h3 id="currentWeekTitle" class="confirmed-calendar-title">
                                <span class="nav-dot" style="background-color: #10b981;"></span> Lịch Đã Chốt (Theo Tuần)
                            </h3>
                            <div class="calendar-action-group">
                                <button type="button" id="weekTodayBtn" class="btn-today">Hôm nay</button>
                                <div class="calendar-nav-group">
                                    <button type="button" id="weekPrevBtn" class="calendar-nav-btn">‹</button>
                                    <button type="button" id="weekNextBtn" class="calendar-nav-btn calendar-nav-btn-bordered">›</button>
                                </div>
                            </div>
                        </div>

                        <!-- Lưới lịch tuần (Time-Grid View) -->
                        <div style="overflow-x: auto;">
                            <div id="weeklyCalendarGrid">
                                <!-- Render bằng JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CỘT PHẢI: Khảo Sát Đang Mở -->
                <div class="right-column-content">
                    <div class="poll-section-header">
                        <h3 class="poll-section-title">
                            <span class="nav-dot" style="background-color: #3b82f6;"></span> Khảo Sát Đang Mở
                        </h3>
                    </div>
                    <div class="event-grid" id="pollEventList"></div>
                </div>

            </div>

            <!-- ===================================================================== -->
            <!-- 3. CÁC MODAL (HỘP THOẠI TƯƠNG TÁC) -->
            <!-- ===================================================================== -->

            <!-- Modal: Tạo Sự Kiện Hoặc Khảo Sát Mới -->
            <div id="createEventModal" class="custom-modal">
                <div class="modal-content modal-horizontal" id="createModalContentContainer">
                    <span class="close-btn" data-modal="createEventModal">&times;</span>
                    <h3>Sự Kiện</h3>

                    <form id="createEventForm">
                        <div id="modalLayoutWrapper" class="modal-grid-layout single-column-layout">

                            <!-- CỘT TRÁI: Cấu hình chung -->
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
                                        <select id="eventStatus" onchange="handleEventStatusChange(this)">
                                            <option value="POLL">POLL (Khảo sát thời gian)</option>
                                            <option value="CONFIRMED" selected>CONFIRMED (Chốt lịch luôn)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Phần cấu hình dành riêng cho Khảo sát (POLL) -->
                                <div id="pollConfigSection" class="config-section" style="display: none;">
                                    <h4>Cấu hình thời gian khảo sát</h4>
                                    <div class="form-row">
                                        <div class="form-group"><label>Ngày bắt đầu</label><input type="date" id="pollStartDate"></div>
                                        <div class="form-group"><label>Ngày kết thúc</label><input type="date" id="pollEndDate"></div>
                                    </div>

                                    <div class="form-row" style="margin-top: 10px;">
                                        <div class="form-group"><label>Giờ mở đầu ngày</label><input type="time" id="dailyStartTime" value="06:00"></div>
                                        <div class="form-group"><label>Giờ kết thúc ngày</label><input type="time" id="dailyEndTime" value="23:59"></div>
                                    </div>

                                    <div class="form-group" style="margin-top: 10px;" id="deadlineConfigSection">
                                        <label style="display: block; font-weight: 600; margin-bottom: 6px;">Thời hạn cho phép điền lịch</label>
                                        <div id="deadlineDaysContainer" style="margin-bottom: 8px;">
                                            <select id="pollDurationDays" class="form-control" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;">
                                                <option value="1">Trong vòng 1 ngày</option>
                                                <option value="3">Trong vòng 3 ngày</option>
                                                <option value="5">Trong vòng 5 ngày</option>
                                                <option value="7" selected>Trong vòng 7 ngày (Mặc định)</option>
                                                <option value="10">Trong vòng 10 ngày</option>
                                                <option value="14">Trong vòng 14 ngày</option>
                                                <option value="30">Trong vòng 30 ngày</option>
                                            </select>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <input type="checkbox" id="noDeadlineCheckbox" onchange="toggleDeadlineInput(this)" style="cursor: pointer; width: 14px; height: 14px;">
                                            <label for="noDeadlineCheckbox" style="font-size: 13px; cursor: pointer; user-select: none; font-weight: 500; color: #374151;">
                                                Không giới hạn thời gian khảo sát
                                            </label>
                                        </div>
                                        <small style="color: #6b7280; font-size: 11px; display: block; margin-top: 4px;">Tính từ thời điểm admin bấm tạo khảo sát.</small>
                                    </div>
                                </div>

                                <!-- Phần cấu hình thời gian diễn ra (CONFIRMED) -->
                                <div id="confirmedConfigSection" class="config-section">
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
                            </div>

                            <!-- CỘT PHẢI: Thành viên & Nhắc nhở -->
                            <div class="modal-col-right" id="modalColRight">
                                <div class="form-group" style="height: 100%; display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 8px;">Thành viên tham gia</label>

                                    <div style="margin-bottom: 8px;">
                                        <select id="filterInstrumentSelector" class="form-control" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;" onchange="filterAvailableMembers(this.value)">
                                            <option value="">-- Lọc theo tất cả nhạc cụ --</option>
                                            <option value="Vocal">Vocal</option>
                                            <option value="Ukulele">Ukulele</option>
                                            <option value="Guitar">Guitar</option>
                                            <option value="Piano">Piano</option>
                                            <option value="Drum">Drum</option>
                                            <option value="Bass">Bass</option>
                                        </select>
                                    </div>

                                    <div style="flex: 1; min-height: 220px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #fff;">
                                        <x-memberSelect id="eventMemberSelector" :members="$allMembers" :selected="[]" />
                                    </div>

                                    <button type="button" id="selectAllMembersBtn" class="btn" style="margin-top: 10px; width: 100%;">Chọn tất cả</button>
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

                        </div>

                        <!-- Cụm nút bấm chung cho Modal Tạo Mới -->
                        <div class="form-actions-group" style="display: flex; gap: 12px; margin-top: 20px; width: 100%;">
                            <button type="submit" id="submitEventBtn" class="btn btn-primary" style="flex: 1;">Tạo sự kiện</button>
                            <button type="button" id="deleteEventInModalBtn" class="btn btn-danger" style="flex: 1; background-color: #dc3545; color: #fff; display: none;">Xóa sự kiện</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal: Chỉnh Sửa Hoặc Xóa Sự Kiện / Khảo Sát -->
            <div id="editEventModal" class="custom-modal">
                <div class="modal-content modal-horizontal" id="editModalContentContainer">
                <span class="close-btn" data-modal="pollMatrixModal">&times;</span>                    <h3>Chỉnh Sửa Sự Kiện Hoặc Khảo Sát</h3>

                    <form id="editEventForm">
                        <div id="editModalLayoutWrapper" class="modal-grid-layout single-column-layout">

                            <!-- CỘT TRÁI: Cấu hình chung -->
                            <div class="modal-col-left">
                                <div class="form-group">
                                    <label>Tiêu đề sự kiện</label>
                                    <input type="text" id="editEventTitle" required placeholder="Ví dụ: Khảo sát lịch tập band...">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Phân loại (Type)</label>
                                        <select id="editEventType">
                                            <option value="PRACTICE">PRACTICE (Lịch tập)</option>
                                            <option value="MEETING">MEETING (Lịch họp)</option>
                                            <option value="SHOW">SHOW (Show diễn)</option>
                                            <option value="EVENT">EVENT (Nội bộ)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Trạng thái khởi tạo</label>
                                        <select id="editEventStatus" onchange="handleEditEventStatusChange(this)">
                                            <option value="POLL">POLL (Khảo sát thời gian)</option>
                                            <option value="CONFIRMED">CONFIRMED (Chốt lịch luôn)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Cấu hình khảo sát (POLL) khi sửa -->
                                <div id="editPollConfigSection" class="config-section" style="display: none;">
                                    <h4>Cấu hình thời gian khảo sát</h4>
                                    <div class="form-row">
                                        <div class="form-group"><label>Ngày bắt đầu</label><input type="date" id="editPollStartDate"></div>
                                        <div class="form-group"><label>Ngày kết thúc</label><input type="date" id="editPollEndDate"></div>
                                    </div>

                                    <div class="form-row" style="margin-top: 10px;">
                                        <div class="form-group"><label>Giờ mở đầu ngày</label><input type="time" id="editDailyStartTime" value="06:00"></div>
                                        <div class="form-group"><label>Giờ kết thúc ngày</label><input type="time" id="editDailyEndTime" value="23:59"></div>
                                    </div>
                                </div>

                                <!-- Cấu hình thời gian diễn ra (CONFIRMED) khi sửa -->
                                <div id="editConfirmedConfigSection" class="config-section">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <h4 style="margin: 0;">Thời gian diễn ra sự kiện</h4>
                                        <label style="font-size: 13px; font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                            <input type="checkbox" id="editAllDayEventCheckbox" style="cursor: pointer;"> Cả ngày
                                        </label>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group"><label>Bắt đầu</label><input type="datetime-local" id="editStartTime"></div>
                                        <div class="form-group"><label>Kết thúc</label><input type="datetime-local" id="editEndTime"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- CỘT PHẢI: Thành viên & Nhắc nhở -->
                            <div class="modal-col-right" id="editModalColRight">
                                <div class="form-group" style="height: 100%; display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 8px;">Thành viên tham gia</label>

                                    <div style="margin-bottom: 8px;">
                                        <select id="editFilterInstrumentSelector" class="form-control" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;" onchange="filterEditAvailableMembers(this.value)">
                                            <option value="">-- Lọc theo tất cả nhạc cụ --</option>
                                            <option value="Vocal">Vocal</option>
                                            <option value="Ukulele">Ukulele</option>
                                            <option value="Guitar">Guitar</option>
                                            <option value="Piano">Piano</option>
                                            <option value="Drum">Drum</option>
                                            <option value="Bass">Bass</option>
                                        </select>
                                    </div>

                                    <div style="flex: 1; min-height: 220px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #fff;">
                                        <x-memberSelect id="editEventMemberSelector" :members="$allMembers" :selected="[]" />
                                    </div>

                                    <button type="button" id="editSelectAllMembersBtn" class="btn" style="margin-top: 10px; width: 100%;">Chọn tất cả</button>
                                </div>

                                <div class="form-group" style="margin-top: 15px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Thời điểm nhắc nhở trước:</label>
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                        <label class="reminder-chip-label"><input type="checkbox" name="editRemindMinutes" value="30"><span>30 phút</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="editRemindMinutes" value="60"><span>1 tiếng</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="editRemindMinutes" value="360"><span>6 tiếng</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="editRemindMinutes" value="720"><span>12 tiếng</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="editRemindMinutes" value="1440"><span>1 ngày</span></label>
                                        <label class="reminder-chip-label"><input type="checkbox" name="editRemindMinutes" value="2880"><span>2 ngày</span></label>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Cụm nút bấm chung cho Modal Chỉnh Sửa -->
                        <div class="form-actions-group" style="display: flex; gap: 12px; margin-top: 20px; width: 100%;">
                            <button type="submit" id="submitEditEventBtn" class="btn btn-primary" style="flex: 1;">Cập nhật thay đổi</button>
                            <button type="button" id="deleteEventInModalBtn" class="btn btn-danger" style="flex: 1; background-color: #dc3545; color: #fff;">Xóa sự kiện</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function toggleDeadlineInput(checkbox) {
                    const container = document.getElementById('deadlineDaysContainer');
                    const select = document.getElementById('pollDurationDays');
                    if (checkbox.checked) {
                        container.style.opacity = '0.4';
                        container.style.pointerEvents = 'none';
                        select.disabled = true;
                    } else {
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';
                        select.disabled = false;
                    }
                }

                function handleEventStatusChange(selectElem) {
                    const pollSection = document.getElementById('pollConfigSection');
                    const confirmedSection = document.getElementById('confirmedConfigSection');
                    const layoutWrapper = document.getElementById('modalLayoutWrapper');

                    if (selectElem.value === 'CONFIRMED') {
                        pollSection.style.display = 'none';
                        confirmedSection.style.display = 'block';
                        layoutWrapper.style.gridTemplateColumns = '1fr';
                        layoutWrapper.style.gap = '20px';
                    } else {
                        pollSection.style.display = 'block';
                        confirmedSection.style.display = 'none';
                        layoutWrapper.style.gridTemplateColumns = '1fr 1fr';
                        layoutWrapper.style.gap = '20px';
                    }
                }

                document.addEventListener("DOMContentLoaded", function() {
                    const eventStatusSelect = document.getElementById('eventStatus');
                    if (eventStatusSelect) {
                        handleEventStatusChange(eventStatusSelect);
                    }
                });
            </script>

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
