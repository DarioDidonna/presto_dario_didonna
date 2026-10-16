@props(['id', 'title', 'text', 'action', 'method' => 'PATCH', 'confirm', 'variant' => 'danger'])
{{-- Finestra di conferma con il form vero al suo interno: senza conferma non parte nessuna richiesta --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border {{ $variant === 'success' ? 'border-success' : 'border-danger' }} shadow-lg">
            <div class="modal-header border-secondary">
                <h2 class="modal-title fs-5 text-white fw-bold" id="{{ $id }}Label">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
            </div>
            <div class="modal-body text-muted-custom">{{ $text }}</div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
                <form action="{{ $action }}" method="POST" class="m-0">
                    @csrf
                    @method($method)
                    <button type="submit" class="btn {{ $variant === 'success' ? 'btn-success text-black' : 'btn-danger' }} fw-bold">{{ $confirm }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
