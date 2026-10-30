<x-layout :title="__('ui.revisor_confirm_title')">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="p-4 rounded-3 border border-secondary-subtle bg-navbar-card shadow-lg text-center">
                    <h1 class="h4 text-white fw-bold mb-3">{{ __('ui.revisor_confirm_title') }}</h1>
                    <p class="text-muted-custom mb-4">{{ __('ui.revisor_confirm_body', ['name' => $user->name]) }}</p>
                    {{-- Stessa firma della pagina: il POST riusa l'indirizzo completo del link --}}
                    <form action="{{ request()->getRequestUri() }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-cyber-gradient text-uppercase fw-bold">{{ __('ui.revisor_mail_button') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
