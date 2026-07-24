<header class="canvas-header">
    <div class="header-left">
        <h2 class="page-title">Sunburst Dashboard</h2>
        <div class="filter-tabs">
            <button class="tab-btn active">Server</button>
            <button class="tab-btn">Overview</button>
        </div>
    </div>
    <div class="header-right" style="display: flex; align-items: center; gap: 16px;">
        <!-- Gọi component thông báo đã tách vào đây -->
        @include('components.notification')
        <div class="status-indicator">
            <span class="pulse-dot"></span> API Active
        </div>
    </div>
</header>
