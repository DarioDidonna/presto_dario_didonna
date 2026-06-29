<x-layout>

    @if (session()->has('errorMessage'))
        <div class="row justify-content-center mt-5">
            <div class="col-12 col-md-6">
                <div
                    class="alert alert-danger text-center shadow rounded border-0 bg-danger bg-opacity-25 text-white d-flex align-items-center justify-content-center p-3">
                    <i class="bi bi-x-circle-fill me-2 fs-5 text-danger"></i>
                    <span class="font-monospace fs-7">{{ session('errorMessage') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="row justify-content-center mt-5 pt-3">
            <div class="col-12 col-md-8 col-lg-6">
                <div
                    class="alert bg-dark text-white shadow-lg rounded border border-info d-flex align-items-center justify-content-between p-3 font-monospace animate-fade-in">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-lag text-neon-cyan fs-4 me-3 animate-pulse"></i>
                        <div>
                            <strong class="text-neon-cyan d-block text-uppercase tracking-wider fs-7 mb-0.5">Richiesta
                                Inviata</strong>
                            <span class="fs-8 text-white-50">{{ session('message') }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none fs-8 ps-2"
                        data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <x-header />

    <x-categories />

    <x-live-activity />

    <section class="position-relative overflow-hidden py-5 bg-main-dark">

        <div class="cyber-glow-1"></div>
        <div class="cyber-glow-2"></div>

        <div class="container position-relative z-1 my-4">

            <div class="row align-items-end mb-5">
                <div class="col-12 col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="cyber-badge-pulse"></span>
                        <span class="text-neon-cyan text-uppercase tracking-widest fs-8 fw-bold">Live Feed</span>
                    </div>
                    <h2 class="display-5 fw-black text-white text-uppercase tracking-tight m-0">
                        Ultimi <span class="text-glow text-neon-cyan">Annunci</span> Pubblicati
                    </h2>
                    <p class="text-muted-custom fs-6 m-0 mt-2 max-w-500">
                        Scorri le ultime commissioni inserite su Presto.it e trova l'affare perfetto.
                    </p>
                </div>
                <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('create.article') }}"
                        class="btn btn-cyber-gradient text-uppercase tracking-wider fs-7 fw-bold">
                        <i class="bi bi-plus-circle-fill me-2"></i>Inserisci
                    </a>
                </div>
            </div>

            <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-lg-3">
                @foreach ($articles as $index => $article)
                    <div class="col d-flex animate-fade-in" style="--delay: {{ $index * 0.1 }}s">
                        <div class="w-100 h-100 p-0 transform-wrapper">
                            <x-card :article="$article" />
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    <x-how-it-works />

    <x-revisor-cta />

</x-layout>
