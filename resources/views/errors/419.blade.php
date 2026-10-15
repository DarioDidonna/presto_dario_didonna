<x-error-page code="419" icon="bi-hourglass-bottom" color="text-neon-amber">
    <a href="{{ url()->previous() }}" class="btn btn-outline-neon-cyan text-uppercase tracking-wider fs-7 fw-bold">
        <i class="bi bi-arrow-clockwise me-2"></i>{{ __('ui.error_retry') }}
    </a>
</x-error-page>
