<header class="canvas-header">
    <div class="header-left">
        <div class="header-left">
            <h1 class="text-modal-title">Xin chào, {{ auth()->user()->name }}</h1>
            <p class="text-modal-subtitle">Chào mừng bạn đến với hệ thống quản lý Sunburst!</p>
        </div>
    </div>

    <div class="header-right">
        @include('components.notification')

        <div class="status-indicator">
            <span class="pulse-dot"></span> API Active
        </div>
    </div>
</header>
