@props([
    'mode' => 'full' // Nhận 'full' cho trang lịch lớn hoặc 'compact' cho modal/widget nhỏ
])

{{-- Thẻ bọc ngoài cùng chứa x-data, quản lý toàn bộ component kể cả Modal --}}
<div class="calendar-page-wrapper" x-data="calendarComponent({ mode: '{{ $mode }}' })">

    {{-- PHẦN HEADER TRANG & NÚT THÊM SỰ KIỆN --}}
    @if($mode === 'full')
    <div class="calendar-top-actions">
        <div class="calendar-page-info">
            <h2>Lịch Hoạt Động & Sự Kiện của SUNBURST</h2>
            <p>CHECK XEM CÓ MISS LỊCH CỦA CLUB KHÔNG NHAAAAAAAAAA</p>
        </div>

        @php
            $user = auth()->user();
            $isManagement = $user && method_exists($user, 'isManagementTier') && $user->isManagementTier();
        @endphp

        @if($isManagement)
            <div class="calendar-action-buttons">
                <button type="button" class="btn btn-add-event" @click="$dispatch('open-add-event-modal')">
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
    <div class="calendar-widget {{ $mode === 'compact' ? 'calendar-compact' : 'calendar-full' }}">

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
            <template x-for="dayName in weekDays" :key="dayName">
                <div class="calendar-day-header" x-text="dayName"></div>
            </template>

            <template x-for="(date, index) in calendarDays" :key="date.fullDate + '_' + index">
                <div class="calendar-day-cell"
                     @click="openDayDetail(date)"
                     :class="{
                         'other-month': !date.isCurrentMonth,
                         'is-today': date.isToday,
                         'has-events': date.events && date.events.length > 0,
                         'single-event-cell': date.events && date.events.length === 1
                     }">

                    <div class="day-number-wrapper">
                        <span class="day-number" x-text="date.dayNumber"></span>
                    </div>

                    <div class="event-list">
                        @if($mode === 'full')
                            <div class="event-items-wrapper">
                                <template x-for="evt in date.events" :key="evt._id || evt.id">
                                    <div class="event-item"
                                         :style="{ backgroundColor: getEventColor(evt.type) }"
                                         @click.stop="selectEvent(evt)"
                                         :title="evt.title">
                                        <span x-text="evt.title"></span>
                                    </div>
                                </template>
                            </div>
                        @else
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

    {{-- MODAL NẰM BÊN TRONG PHẠM VI x-data ĐỂ NHẬN ĐƯỢC BIẾN showDayModal --}}
    <div x-show="showDayModal"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showDayModal = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[85vh] flex flex-col"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">

            <!-- Header Modal -->
            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800" x-text="'Chi tiết ngày: ' + selectedDateFormatted"></h3>
                <button @click="showDayModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            </div>

            <!-- Danh sách sự kiện trong ngày -->
            <div class="py-4 overflow-y-auto flex-1 space-y-3">
                <template x-if="selectedDayEvents.length === 0">
                    <p class="text-center text-slate-400 py-8 text-sm">Không có sự kiện nào trong ngày này.</p>
                </template>

                <template x-for="evt in selectedDayEvents" :key="evt._id || evt.id">
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 flex justify-between items-start gap-3">
                        <div>
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-md text-white mb-1"
                                  :style="{ backgroundColor: getEventColor(evt.type) }"
                                  x-text="evt.type || 'Sự kiện'"></span>
                            <h4 class="font-bold text-slate-800 text-sm" x-text="evt.title"></h4>
                            <p class="text-xs text-slate-500 mt-1" x-text="evt.description || 'Không có mô tả'"></p>
                        </div>

                        @if($isManagement ?? false)
                        <div class="flex items-center gap-1 shrink-0">
                            <button @click="editEvent(evt)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg text-xs font-semibold">Sửa</button>
                            <button @click="deleteEvent(evt)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg text-xs font-semibold">Xóa</button>
                        </div>
                        @endif
                    </div>
                </template>
            </div>

            <!-- Footer Modal -->
            @if($isManagement ?? false)
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button @click="addEventForDate(selectedDateFull)" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition">
                    + Thêm sự kiện vào ngày này
                </button>
            </div>
            @endif
        </div>
    </div>

</div> {{-- Đóng thẻ calendar-page-wrapper chứa toàn bộ x-data --}}
