@php
    // Il percorso reale di un oggetto sul sito: chi fa cosa, dalla pubblicazione alla vendita
    $steps = [
        ['key' => 'publish', 'icon' => 'bi-pencil-square', 'tone' => 'how-step-cyan'],
        ['key' => 'review', 'icon' => 'bi-shield-check', 'tone' => 'how-step-amber'],
        ['key' => 'online', 'icon' => 'bi-eye', 'tone' => 'how-step-cyan'],
        ['key' => 'request', 'icon' => 'bi-bag-check', 'tone' => 'how-step-blue'],
        ['key' => 'sold', 'icon' => 'bi-hand-thumbs-up', 'tone' => 'how-step-green'],
    ];
@endphp
<section class="presto-how-section py-5 position-relative" id="howItWorksSection">
    <div class="container position-relative z-index-2 py-lg-4">

        <div class="row mb-5 justify-content-center">
            <div class="col-12 col-md-8 text-center">
                <div class="d-inline-flex align-items-center gap-2 how-pre-title px-3 py-1 rounded-pill mb-2">
                    <i class="bi bi-signpost-split-fill text-neon-cyan fs-7" aria-hidden="true"></i>
                    <span class="text-uppercase tracking-wider fs-8 fw-bold text-white-50">{{ __('ui.how_eyebrow') }}</span>
                </div>
                <h2 class="fw-black text-white text-uppercase tracking-tight display-6 mb-3">
                    {{ __('ui.how_it_works') }} <span class="text-glow text-neon-cyan">Presto</span>
                </h2>
                <p class="text-muted-custom fs-6 max-w-540 mx-auto">{{ __('ui.how_it_works_description') }}</p>
            </div>
        </div>

        {{-- Linea del percorso: orizzontale da desktop, verticale su schermi stretti (stile in style.css, sezione HOW) --}}
        <ol class="how-path list-unstyled m-0 row g-4 row-cols-1 row-cols-lg-5">
            @foreach ($steps as $step)
                <li class="col how-step {{ $step['tone'] }}">
                    <div class="step-card h-100 p-4 position-relative transition-all">
                        <div class="step-number-badge font-monospace fw-bold" aria-hidden="true">0{{ $loop->iteration }}</div>
                        <div class="step-icon-wrapper how-step-icon d-flex align-items-center justify-content-center mb-3">
                            <i class="bi {{ $step['icon'] }} fs-4" aria-hidden="true"></i>
                        </div>
                        <span class="how-step-role d-block text-uppercase tracking-wider fs-8 fw-bold mb-1">{{ __('ui.how_'.$step['key'].'_role') }}</span>
                        <h3 class="text-white fw-bold fs-6 text-uppercase tracking-wide mb-2">
                            <span class="visually-hidden">{{ $loop->iteration }}.</span> {{ __('ui.how_'.$step['key'].'_title') }}
                        </h3>
                        <p class="text-muted-custom fs-7 mb-0">{{ __('ui.how_'.$step['key'].'_text') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

    </div>
</section>
