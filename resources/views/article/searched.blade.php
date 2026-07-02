<x-layout>
    <div class="container-fluid bg-light border-bottom py-4 pt-5">
        <div class="container">
            <div class="row align-items-center my-5">
                <div class="col-12 col-md-8 text-center text-md-start">
                    <p class="text-uppercase text-muted fw-bold small mb-1">Esplora il Marketplace</p>
                    <h1 class="fw-bold display-5 mb-2">
                        Risultati per: <span class="text-primary">"{{ $query }}"</span>
                    </h1>
                </div>
                <div class="col-12 col-md-4 text-center text-md-end mt-3 mt-md-0">
                    <span class="badge bg-dark rounded-pill px-3 py-2 fs-6">
                        {{ $articles->total() }} {{ $articles->total() == 1 ? 'annuncio trovato' : 'annunci trovati' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row min-vh-50 justify-content-start py-4">
            @forelse ($articles as $article)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 d-flex align-items-stretch">
                    <div class="w-100 shadow-sm rounded border p-2 bg-white card-hover-effect">
                        <x-card :article="$article" />
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-search-heart text-muted display-1"></i>
                    </div>
                    <h2 class="fw-bold text-secondary">Nessun articolo corrisponde alla tua ricerca</h2>
                    <p class="text-muted mb-4">Controlla se ci sono errori di battitura o prova a cercare un termine diverso.</p>
                    <a href="{{ route('article.index') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-arrow-left me-2"></i>Torna a tutti gli annunci
                    </a>
                </div>
            @endforelse
        </div>

        @if($articles->hasPages())
            <div class="row justify-content-center border-top pt-4 mt-5">
                <div class="col-auto">
                    {{ $articles->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>

