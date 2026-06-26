<section class="presto-categories-section py-5 position-relative" id="categoriesSection">
    <div class="container position-relative z-index-2">

        <div class="row mb-5 justify-content-center">
            <div class="col-12 col-md-8 text-center">
                <div class="d-inline-flex align-items-center gap-2 category-pre-title px-3 py-1 rounded-pill mb-2">
                    <i class="bi bi-tags-fill text-neon-cyan fs-7"></i>
                    <span class="text-uppercase tracking-wider fs-8 fw-bold text-white-50">Sfoglia il catalogo</span>
                </div>
                <h2 class="fw-black text-white text-uppercase tracking-tight display-6 mb-3">
                    Macro <span class="text-glow text-neon-cyan">Categorie</span>
                </h2>
                <p class="text-muted-custom fs-6 max-w-540 mx-auto">
                    Seleziona una delle aree principali del nostro portale per trovare in pochi istanti gli annunci e le
                    commissioni più rilevanti per te.
                </p>
            </div>
        </div>

        <div class="row g-4 justify-content-center row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6">
            
            @foreach($categories as $category)
                <div class="col d-flex">
                    <a href="{{ route('byCategory', ['category' => $category]) }}" class="category-card-wrapper d-flex flex-column align-items-center justify-content-between text-decoration-none text-center p-4 w-100 transition-all">
                        
                        <div class="category-icon-box mb-3 position-relative mx-auto d-flex align-items-center justify-content-center">
                            <i class="{{ $category->icon ?? 'bi bi-cpu' }} fs-2 text-neon-cyan transition-all"></i>
                            <div class="icon-glow-layer"></div>
                        </div>

                        <div>
                            <h5 class="category-title text-white fw-semibold mb-1 fs-7 text-uppercase tracking-wide truncate" title="{{ $category->name }}">
                                {{ $category->name }}
                            </h5>
                            
                            <span class="category-counter text-muted-custom fs-8 font-monospace">
                                {{ $category->articles->count() }} {{ $category->articles->count() == 1 ? 'Annuncio' : 'Annunci' }}
                            </span>
                        </div>

                    </a>
                </div>
            @endforeach

        </div>
    </div>
</section>