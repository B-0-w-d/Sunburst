@props([
    'id',           // Unique HTML ID for the modal (e.g. 'editMemberModal')
    'title',        // Modal Title header text
    'maxWidth' => '460px', // Default width, customizable per instance
    'submitFn' => '' // Optional inline submit function (e.g. 'submitEditForm(event)')
])

<div class="modal-backdrop" id="{{ $id }}">
    <div class="modal-window" style="max-width: {{ $maxWidth }};">
        <div class="modal-header">
            <h4 class="modal-title">{{ $title }}</h4>
            <button class="modal-close-btn" onclick="closeModal('{{ $id }}')">&times;</button>
        </div>

        @if($submitFn)
            <form id="{{ $id }}Form" onsubmit="{{ $submitFn }}">
        @endif

            <div class="modal-body">
                {{ $slot }} {{-- This is where your custom form fields will render dynamically --}}
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('{{ $id }}')">Cancel</button>
                {{ $footer ?? '' }} {{-- Dynamic spot for custom actions/buttons --}}
            </div>

        @if($submitFn)
            </form>
        @endif
    </div>
</div>
