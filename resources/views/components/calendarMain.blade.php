@props([
    'mode' => 'full' // Nhận 'full' cho trang lớn hoặc 'compact' cho Modal nhỏ
])

<div class="calendar-page-wrapper">
    {{-- PHẦN HEADER TRANG & NÚT THÊM SỰ KIỆN (Chỉ hiển thị cho BQL) --}}
    @if($mode === 'full')
    <div class="calendar-top-actions">
        <div class="calendar-page-info">
            <h2>Lịch Hoạt Động & Sự Kiện của SUNBURST</h2>
            <p>CHECK XEM CÓ MISS LỊCH CỦA CLUB KHÔNG NHAAAAAAAAAA</p>
        </div>

        {{-- Kiểm tra user hiện tại có quyền management tier hay không --}}
        @php
            $user = auth()->user();
            $isManagement = $user && method_exists($user, 'isManagementTier') && $user->isManagementTier();
        @endphp

        @if($isManagement)
            <div class="calendar-action-buttons">
                <button type="button" class="btn-add-event" @click="$dispatch('open-add-event-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Thêm Sự Kiện
                </button>
            </div>
        @endif
    </div>
    @endif

    {{-- WIDGET LỊCH CHÍNH --}}
    <div class="calendar-widget {{ $mode === 'compact' ? 'calendar-compact' : 'calendar-full' }}"
         x-data="calendarComponent({ mode: '{{ $mode }}' })">

        <!-- Calendar Header -->
        <div class="calendar-header">
            <div class="calendar-title">
                <h4 x-text="currentMonthYear">--/----</h4>
                <span x-show="isLoading" class="loading-spinner" style="display: none;">Đang tải...</span>
            </div>
            <div class="calendar-controls">
                <button type="button" @click="prevMonth()" class="btn-cal-nav">&lt;</button>
                <button type="button" @click="today()" class="btn-cal-today">Hôm nay</button>
                <button type="button" @click="nextMonth()" class="btn-cal-nav">&gt;</button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <!-- Header Các Ngày Trong Tuần -->
            <template x-for="dayName in weekDays" :key="dayName">
                <div class="calendar-day-header" x-text="dayName"></div>
            </template>

            <!-- Ô Ngày Trong Tháng -->
            <template x-for="(date, index) in calendarDays" :key="date.fullDate + '_' + index">
                <div class="calendar-day-cell"
                     :class="{
                         'other-month': !date.isCurrentMonth,
                         'is-today': date.isToday,
                         'has-events': date.events && date.events.length > 0
                     }">

                    <div class="day-number-wrapper">
                        <span class="day-number" x-text="date.dayNumber"></span>
                    </div>

                    <!-- Event List -->
                    <div class="event-list">
                        @if($mode === 'full')
                            {{-- TRANG LỚN: Thanh sự kiện kèm tiêu đề --}}
                            <div class="event-items-wrapper">
                                <template x-for="evt in date.events" :key="evt._id || evt.id">
                                    <div class="event-item"
                                         :style="{ backgroundColor: getEventColor(evt.type) }"
                                         @click="selectEvent(evt)"
                                         :title="evt.title">
                                        <span x-text="evt.title"></span>
                                    </div>
                                </template>
                            </div>
                        @else
                            {{-- MODAL NHỎ: Chấm màu biểu thị --}}
                            <div class="event-dots">
                                <template x-for="evt in date.events" :key="evt._id || evt.id">
                                    <span class="event-dot"
                                          :style="{ backgroundColor: getEventColor(evt.type) }"
                                          :title="evt.title"></span>
                                </template>
                            </div>
                        @endif
                    </div>

                </div>
            </template>
        </div>
    </div>
</div>
