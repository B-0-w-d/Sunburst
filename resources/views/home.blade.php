<x-navbar title="Welcome to Sunburst">
    <div class="home-view-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <div>

        </div>
    </div>
</x-navbar>

{{-- Sử dụng @push('scripts') để đẩy đoạn script cấu hình modal vào stack scripts của layout chính thay vì viết trần trên view --}}
@push('scripts')
<script>
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('is-open');
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('is-open');
    };
</script>
@endpush
