<x-layout>

    <div class="position-relative overflow-hidden bg-navbar py-5 border-bottom border-secondary-subtle">
        <div
            class="position-absolute top-50 start-50 translate-middle w-50 h-50 bg-glow-radial opacity-10 pointer-events-none">
        </div>

        <div class="container position-relative z-1 py-4 text-center">
            <span
                class="badge bg-transparent border border-neon-cyan text-neon-cyan text-uppercase tracking-widest px-3 py-2 mb-3 fs-8 fw-semibold">
                Bacheca Globale
            </span>
            <h1 class="display-4 fw-black text-white text-uppercase tracking-tight mb-2">
                Tutti gli <span class="text-glow text-neon-cyan">Annunci</span>
            </h1>
            <p class="text-muted-custom fs-5 max-w-600 mx-auto mb-0">
                Esplora tutte le commissioni e i prodotti disponibili sulla piattaforma di Presto.it
            </p>
        </div>
    </div>

    <div class="article-index-wrapper py-5">
        <div class="container">

            @if ($articles->count() > 0)
                <div class="row g-4">
                    @foreach ($articles as $article)
                        <div class="col-12 col-md-6 col-lg-4 d-flex">
                            <x-card :article="$article" />
                        </div>
                    @endforeach
                </div>

                @if (method_exists($articles, 'links'))
                    <div class="d-flex justify-content-center mt-5 pagination-cyber">
                        {{ $articles->links() }}
                    </div>
                @endif
            @else
                <div class="row justify-content-center py-5">
                    <div
                        class="col-12 col-md-6 text-center py-5 border border-dashed border-secondary rounded-4 bg-navbar-dark shadow">
                        <div class="fs-1 text-muted mb-3">
                            <i class="bi bi-folder-x text-neon-amber"></i>
                        </div>
                        <h4 class="text-white fw-bold text-uppercase tracking-wide mb-2">Nessun annuncio trovato</h4>
                        <p class="text-muted-custom fs-7 mb-4">Non ci sono ancora articoli pubblicati in questa sezione.
                            Sii il primo a inserirne uno!</p>
                        <a href="{{ route('articles.create') }}"
                            class="btn btn-outline-neon-cyan text-uppercase tracking-wider fs-7 fw-bold px-4 py-2">
                            <i class="bi bi-plus-circle me-2"></i> Crea Annuncio
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

</x-layout>
