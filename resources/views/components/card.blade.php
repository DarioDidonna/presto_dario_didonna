<div class="card bg-navbar border-neon-cyan shadow-lg h-100 transition-all card-hover">
    <img src="{{ $article->images->isNotEmpty() ? Storage::url($article->images->first()->path) : 'https://picsum.photos/300/200' }}" class="card-img-top border-bottom border-secondary-subtle" alt="{{ $article->title }}">
    
    <div class="card-body d-flex flex-column p-4">
        <div class="mb-2">
            @if($article->category)
                <a href="{{ route('byCategory', ['category' => $article->category]) }}" class="badge bg-transparent border border-neon-amber text-neon-amber text-uppercase tracking-wider fs-9 text-decoration-none transition-all hover-glow-amber">
                    <i class="bi bi-tag-fill me-1"></i> {{ $article->category->name }}
                </a>
            @else
                <span class="badge bg-transparent border border-secondary text-muted fs-9 text-uppercase tracking-wider">
                    {{ __('ui.no_category') }}
                </span>
            @endif
        </div>

        <h5 class="card-title fw-black text-white text-uppercase tracking-tight text-truncate mb-2" title="{{ $article->title }}">
            {{ $article->title }}
        </h5>
        
        <p class="card-text text-muted-custom fs-7 flex-grow-1 text-truncate-2">
            {{ $article->description }}
        </p>

        <div class="d-flex align-items-center justify-content-between pt-3 mt-auto border-top border-secondary-subtle">
            <span class="fs-5 fw-bold text-glow text-neon-cyan font-monospace">
                {{ number_format($article->price, 2, ',', '.') }} €
            </span>
            
            <a href="{{ route('article.show', compact('article')) }}" class="btn btn-sm btn-outline-neon-cyan text-uppercase tracking-wide fs-8 fw-bold transition-all">
                {{ __('ui.details') }} <i class="bi bi-arrow-right-short ms-1"></i>
            </a>
        </div>
    </div>
</div>