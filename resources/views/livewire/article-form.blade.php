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
            <label for="title"
                class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.title') }}</label>
            <input type="text" wire:model="title" id="title"
                class="form-control bg-dark border-secondary text-white @error('title') is-invalid @enderror"
                placeholder="{{ __('ui.placeholder_title') }}">
            @error('title')
                <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label for="price"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.price_label') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-neon-cyan">€</span>
                    <input type="number" step="0.01" wire:model="price" id="price"
                        class="form-control bg-dark border-secondary text-white @error('price') is-invalid @enderror"
                        placeholder="0.00">
                    @error('price')
                        <div class="invalid-feedback font-monospace fs-8 d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-md-6 mb-3">
                <label for="category"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.category') }}</label>
                <select wire:model="category" id="category"
                    class="form-select bg-dark border-secondary text-white @error('category') is-invalid @enderror">
                    <option value="">{{ __('ui.select_category') }}</option>

                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="temporary_images"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">Immagini
                    dell'annuncio</label>
                <input type="file" name="images" wire:model="temporary_images" multiple
                    class="form-control shadow @error('temporary_images.*') is-invalid @enderror @error('temporary_images') is-invalid @enderror"
                    placeholder="Img/">

                @error('temporary_images.*')
                    <p class="fst-italic text-danger small mt-1">{{ $message }}</p>
                @enderror

                @error('temporary_images')
                    <p class="fst-italic text-danger small mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if (!empty($images))
                <div class="col-12 mt-4">
                    <p class="text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan mb-2">
                        <i class="bi bi-cpu text-neon-cyan me-1"></i> Photo preview:
                    </p>

                    <div
                        class="row border border-secondary rounded shadow-lg py-4 bg-black bg-opacity-50 mx-0 justify-content-start align-items-center">
                        @foreach ($images as $key => $image)
                            <div
                                class="col-6 col-sm-4 col-md-3 d-flex flex-column align-items-center my-3 position-relative preview-container">

                                <div
                                    class="img-preview shadow rounded border border-secondary position-relative overflow-hidden">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-100 h-100 object-fit-cover"
                                        alt="Preview">

                                    <button type="button"
                                        class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center border-0 position-absolute top-0 end-0 m-1 rounded-circle btn-remove-preview"
                                        wire:click="removeImage({{ $key }})" title="Rimuovi">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="mb-4">
            <label for="description"
                class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.description_label') }}</label>
            <textarea wire:model="description" id="description" rows="5"
                class="form-control bg-dark border-secondary text-white @error('description') is-invalid @enderror"
                placeholder="{{ __('ui.description_placeholder') }}"></textarea>
            @error('description')
                <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
            @enderror
        </div>

        <div class="text-end">
            <button type="submit"
                class="btn btn-outline-neon-cyan px-4 py-2 text-uppercase tracking-wider fs-7 fw-bold transition-all">
                <span wire:loading.remove wire:target="store">
                    <i class="bi bi-plus-circle me-2"></i> {{ __('ui.publish_ad') }}
                </span>
                <span wire:loading wire:target="store">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ __('ui.processing') }}
                </span>
            </button>
        </div>

    </form>

</div>
