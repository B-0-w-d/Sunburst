<x-navbar>
    <div class="dashboard-layout-wrapper" style="display: flex; gap: 24px; align-items: flex-start; width: 100%;">

        {{-- Sidebar bên trái --}}
        <aside class="nav-sidebar" style="width: 260px; flex-shrink: 0; background: #ffffff; padding: 20px; box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);">
            {{-- Danh sách các dự án sắp tới --}}
            <div class="sidebar-section" style="margin-bottom: 24px;">
                <div class="section-header" style="margin-bottom: 12px;">
                    <span class="section-title" style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Upcoming shows</span>
                </div>
                <div class="project-list" style="display: flex; flex-direction: column; gap: 4px;">
                    <a href="#" class="project-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; color: #475569; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                        <span class="dot dot-blue" style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span> Campaigns
                    </a>
                    <a href="#" class="project-item active" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; color: #0f172a; background: #f8fafc; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                        <span class="dot dot-red" style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span> Publications
                    </a>
                    <a href="#" class="project-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; color: #475569; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                        <span class="dot dot-green" style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span> Development
                    </a>
                </div>
            </div>

            {{-- Thẻ thông báo sự kiện sắp tới (Động) --}}
            <div class="card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 12px; padding: 16px; border: 1px solid #bfdbfe;">
                <span class="promo-tag" style="font-size: 0.7rem; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.05em; background: #ffffff; padding: 2px 8px; border-radius: 99px; display: inline-block; margin-bottom: 8px;">
                    Sắp diễn ra
                </span>

                @if(isset($upcomingEvent) && $upcomingEvent)
                    <h4 class="promo-title" style="font-size: 0.9rem; font-weight: 700; color: #1e293b; margin: 0 0 4px 0; line-height: 1.3;">
                        {{ $upcomingEvent->title }}
                    </h4>
                    <p class="promo-meta" style="font-size: 0.75rem; color: #64748b; margin: 0 0 12px 0;">
                        {{ \Carbon\Carbon::parse($upcomingEvent->start_time)->format('d/m/Y H:i') }}
                    </p>
                    <a href="#" class="promo-btn" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 600; color: #2563eb; text-decoration: none;">
                        Xem chi tiết <span class="arrow">→</span>
                    </a>
                @else
                    <h4 class="promo-title" style="font-size: 0.9rem; font-weight: 700; color: #1e293b; margin: 0 0 4px 0; line-height: 1.3;">
                        Không có sự kiện mới
                    </h4>
                    <p class="promo-meta" style="font-size: 0.75rem; color: #64748b; margin: 0 0 12px 0;">
                        Hãy theo dõi lịch để cập nhật hoạt động nhé!
                    </p>
                @endif
            </div>
        </aside> {{-- ĐÃ SỬA: Chỉ giữ lại 1 thẻ đóng aside duy nhất ở đây --}}

        {{-- Phần nội dung chứa lịch bên phải --}}
        <div class="calendar-page-container" style="flex: 1; min-width: 0; padding-top: 25px; padding-right: 25px">
            <x-calendarMain mode="full" />
        </div>

    </div>
</x-navbar>
