@props([
    'id',           // Unique HTML ID for the modal
    'title',        // Modal Title header text
    'maxWidth' => '460px',
    'submitFn' => ''
])

{{-- Changed class from modal-backdrop to modal --}}
<div class="modal" id="{{ $id }}">
    <div class="modal-window" style="max-width: {{ $maxWidth }};">
        <div class="modal-header">
            <h4 class="modal-title">{{ $title }}</h4>
            <button class="modal-close-btn" onclick="closeModal('{{ $id }}')">&times;</button>
        </div>

        @if($submitFn)
            <form id="{{ $id }}Form" onsubmit="{{ $submitFn }}">
        @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('{{ $id }}')">Cancel</button>
                {{ $footer ?? '' }}
            </div>

        @if($submitFn)
            </form>
        @endif
    </div>
</div>
