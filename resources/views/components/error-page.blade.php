@props(['code', 'icon' => 'bi-exclamation-triangle', 'color' => 'text-neon-cyan'])
{{-- Pagina autonoma: niente navbar né query, così funziona anche se l'errore riguarda il database.
     La lingua la imposta ErrorPageLocaleComposer. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/png" href="{{ asset('/media/favicon.png') }}">
    @vite(['resources/css/app.css'])
    <title>{{ $code }} · {{ __('ui.error_' . $code . '_title') }} · Presto.it</title>
</head>
<body>
    <main class="min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden py-5 px-3">
        <div class="cyber-glow-1"></div>
        <div class="cyber-glow-2"></div>

        <div class="text-center position-relative z-1 max-w-540">
            <a href="{{ url('/') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-5">
                <i class="bi bi-layers-half text-neon-cyan fs-4"></i>
                <span class="fw-bold tracking-wider text-uppercase text-white text-glow fs-5">
                    PRESTO<span class="text-neon-cyan">.</span>IT
                </span>
            </a>

            <div class="mb-3"><i class="bi {{ $icon }} {{ $color }} display-3"></i></div>
            <p class="font-monospace {{ $color }} fs-1 fw-bold mb-1 text-glow">{{ $code }}</p>
            <h1 class="h3 fw-black text-white text-uppercase tracking-tight mb-3">{{ __('ui.error_' . $code . '_title') }}</h1>
            <p class="text-muted-custom mb-4">{{ __('ui.error_' . $code . '_text') }}</p>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-cyber-gradient text-uppercase tracking-wider fs-7 fw-bold">
                    <i class="bi bi-house-door me-2"></i>{{ __('ui.go_homepage') }}
                </a>
                {{ $slot }}
            </div>
        </div>
    </main>
</body>
</html>
