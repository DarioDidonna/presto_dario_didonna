<div>
    {{-- It is never too late to be what you might have been. - George Eliot --}}

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show bg-success-subtle text-success border-success-subtle rounded-3 mb-4"
            role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    <form wire:submit.prevent="store">

        <div class="mb-3">
            <label for="title" class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-white-50">Titolo
                Annuncio</label>
            <input type="text" wire:model.blur="title" id="title"
                class="form-control bg-dark border-secondary text-white @error('title') is-invalid @enderror"
                placeholder="Es. iPhone 15 Pro Max 256GB">
            @error('title')
                <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label for="price"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-white-50">Prezzo
                    (€)</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-white-50">€</span>
                    <input type="number" step="0.01" wire:model.blur="price" id="price"
                        class="form-control bg-dark border-secondary text-white @error('price') is-invalid @enderror"
                        placeholder="0.00">
                    @error('price')
                        <div class="invalid-feedback font-monospace fs-8 d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-md-6 mb-3">
                <label for="category"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-white-50">Categoria</label>
                <select wire:model.blur="category" id="category"
                    class="form-select bg-dark border-secondary text-white @error('category') is-invalid @enderror">
                    <option value="">Seleziona una categoria</option>

                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="description"
                class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-white-50">Descrizione del
                servizio o prodotto</label>
            <textarea wire:model.blur="description" id="description" rows="5"
                class="form-control bg-dark border-secondary text-white @error('description') is-invalid @enderror"
                placeholder="Fornisci quanti più dettagli possibili..."></textarea>
            @error('description')
                <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
            @enderror
        </div>

        <div class="text-end">
            <button type="submit"
                class="btn btn-outline-neon-cyan px-4 py-2 text-uppercase tracking-wider fs-7 fw-bold transition-all">
                <span wire:loading.remove wire:target="store">
                    <i class="bi bi-plus-circle me-2"></i> Pubblica Annuncio
                </span>
                <span wire:loading wire:target="store">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Elaborazione...
                </span>
            </button>
        </div>

    </form>

</div>
