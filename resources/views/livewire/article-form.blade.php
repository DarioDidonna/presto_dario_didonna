<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show bg-success-subtle text-success border-success-subtle rounded-3 mb-4"
            role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('ui.close') }}"></button>
        </div>
    @endif


    {{-- Vista condivisa da creazione (store) e modifica (update) annuncio --}}
    <form wire:submit.prevent="{{ $submitAction }}">

        <div class="mb-3">
            <label for="title"
                class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.title') }}</label>
            <input type="text" wire:model.blur="form.title" id="title"
                class="form-control bg-dark border-secondary text-white @error('form.title') is-invalid @enderror"
                placeholder="{{ __('ui.placeholder_title') }}">
            @error('form.title')
                <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label for="price"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.price_label') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-neon-cyan">€</span>
                    <input type="number" step="0.01" min="0.01" wire:model.blur="form.price" id="price"
                        class="form-control bg-dark border-secondary text-white @error('form.price') is-invalid @enderror"
                        placeholder="0.00">
                    @error('form.price')
                        <div class="invalid-feedback font-monospace fs-8 d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-md-6 mb-3">
                <label for="category"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.category') }}</label>
                <select wire:model.live="form.category" id="category"
                    class="form-select bg-dark border-secondary text-white @error('form.category') is-invalid @enderror">
                    <option value="">{{ __('ui.select_category') }}</option>

                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->translated_name }}</option>
                    @endforeach
                </select>
                @error('form.category')
                    <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
                @enderror
            </div>

            @if ($existingImages->isNotEmpty())
                <div class="mb-3">
                    <p class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan mb-2">{{ __('ui.current_images') }}</p>
                    <div class="row g-2">
                        @foreach ($existingImages as $existing)
                            @php($marked = in_array($existing->id, $imagesToRemove, true))
                            <div class="col-4 col-sm-3 col-md-2" wire:key="existing-{{ $existing->id }}">
                                <div class="position-relative rounded overflow-hidden border {{ $marked ? 'border-danger' : 'border-secondary' }}">
                                    <img src="{{ $existing->thumbnailUrl() }}" alt="{{ __('ui.image_number', ['n' => $loop->iteration]) }}"
                                        class="w-100 object-fit-cover {{ $marked ? 'opacity-25' : '' }}" style="aspect-ratio: 1 / 1;">
                                    <button type="button" wire:click="toggleExistingImage({{ $existing->id }})"
                                        class="btn btn-sm {{ $marked ? 'btn-secondary' : 'btn-danger' }} p-0 d-flex align-items-center justify-content-center border-0 position-absolute top-0 end-0 m-1 rounded-circle btn-remove-preview"
                                        title="{{ $marked ? __('ui.keep_image') : __('ui.remove') }}"
                                        aria-label="{{ $marked ? __('ui.keep_image') : __('ui.remove') }}" aria-pressed="{{ $marked ? 'true' : 'false' }}">
                                        <i class="bi {{ $marked ? 'bi-arrow-counterclockwise' : 'bi-x-lg' }}"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if (!empty($imagesToRemove))
                        <p class="text-danger fs-8 mt-2 mb-0">{{ __('ui.images_marked_for_removal', ['count' => count($imagesToRemove)]) }}</p>
                    @endif
                </div>
            @endif

            <div class="mb-3">
                <label for="temporary_images"
                    class="form-label text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan">{{ __('ui.images_label') }}</label>
                <input type="file" id="temporary_images" name="images" wire:model="temporary_images" multiple accept="image/*"
                    class="form-control shadow @error('temporary_images.*') is-invalid @enderror @error('temporary_images') is-invalid @enderror">

                <div wire:loading wire:target="temporary_images" class="text-neon-cyan fs-8 font-monospace mt-2" role="status">
                    <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> {{ __('ui.uploading_images') }}
                </div>

                @error('temporary_images.*')
                    <p class="fst-italic text-danger small mt-1">{{ $message }}</p>
                @enderror

                @error('temporary_images')
                    <p class="fst-italic text-danger small mt-1">{{ $message }}</p>
                @enderror

                @error('images')
                    <p class="fst-italic text-danger small mt-1">{{ $message }}</p>
                @enderror

                @error('images.*')
                    <p class="fst-italic text-danger small mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if (!empty($images))
                <div class="col-12 mt-4">
                    <p class="text-uppercase tracking-wide fs-8 fw-semibold text-neon-cyan mb-2">
                        <i class="bi bi-cpu text-neon-cyan me-1"></i> {{ __('ui.photo_preview') }}
                    </p>

                    <div
                        class="row border border-secondary rounded shadow-lg py-4 bg-black bg-opacity-50 mx-0 justify-content-start align-items-center">
                        @foreach ($images as $key => $image)
                            <div
                                class="col-6 col-sm-4 col-md-3 d-flex flex-column align-items-center my-3 position-relative preview-container">

                                <div
                                    class="img-preview shadow rounded border border-secondary position-relative overflow-hidden">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-100 h-100 object-fit-cover"
                                        alt="{{ __('ui.image_number', ['n' => $key + 1]) }}">

                                    <button type="button"
                                        class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center border-0 position-absolute top-0 end-0 m-1 rounded-circle btn-remove-preview"
                                        wire:click="removeImage({{ $key }})" title="{{ __('ui.remove') }}">
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
            <textarea wire:model.blur="form.description" id="description" rows="5"
                class="form-control bg-dark border-secondary text-white @error('form.description') is-invalid @enderror"
                placeholder="{{ __('ui.description_placeholder') }}"></textarea>
            @error('form.description')
                <div class="invalid-feedback font-monospace fs-8">{{ $message }}</div>
            @enderror
        </div>

        <div class="text-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submitAction }}, temporary_images"
                class="btn btn-outline-neon-cyan px-4 py-2 text-uppercase tracking-wider fs-7 fw-bold transition-all">
                <span wire:loading.remove wire:target="{{ $submitAction }}">
                    <i class="bi {{ $submitIcon }} me-2"></i> {{ $submitLabel }}
                </span>
                <span wire:loading wire:target="{{ $submitAction }}">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ __('ui.processing') }}
                </span>
            </button>
        </div>

    </form>

</div>
