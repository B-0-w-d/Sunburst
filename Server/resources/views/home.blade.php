<x-navbar title="Welcome to Sunburst">
    <div class="home-view-wrapper" style="background-image: url('{{ asset('images/login-background.jpg') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
        <div>
</x-navbar>
<script>
        window.openModal = function(modalId) {
            document.getElementById(modalId).classList.add('is-open');
        };

        window.closeModal = function(modalId) {
            document.getElementById(modalId).classList.remove('is-open');
        };
    </script>
