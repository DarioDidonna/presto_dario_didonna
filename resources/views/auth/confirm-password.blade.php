<x-layout :title="__('ui.confirm_password_title')">
    <div class="min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden pt-custom-navbar pb-5">

        <div class="presto-card-glow"></div>

        <div class="container position-relative z-index-2">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                    <div class="presto-auth-card p-4 p-sm-5 shadow-lg">
                        <div class="text-center mb-4">
                            <div class="presto-icon-wrapper mb-3">
                                <i class="bi bi-shield-check text-accent-cyan fs-3"></i>
                            </div>
                            <h1 class="fw-black text-white text-uppercase tracking-tight h4 mb-1">{{ __('ui.confirm_password_title') }}</h1>
                            <p class="text-muted-custom fs-7 mb-0">{{ __('ui.confirm_password_subtitle') }}</p>
                        </div>

                        <form method="POST" action="{{ route('password.confirm.store') }}" novalidate>
                            @csrf

                            <div class="mb-4">
                                <label for="password" class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ __('ui.password') }}</label>
                                <div class="presto-input-group d-flex align-items-center px-3 {{ $errors->has('password') ? 'is-invalid-group' : '' }}">
                                    <i class="bi bi-lock text-neon-cyan me-2"></i>
                                    <input type="password" name="password" id="password" required autofocus autocomplete="current-password"
                                        class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100">
                                    <button type="button" class="btn btn-toggle-password p-0 border-0 bg-transparent"
                                        data-toggle-password="password" aria-label="{{ __('ui.show_password') }}" aria-pressed="false">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback-custom mt-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-info text-dark w-100 rounded-pill py-2 fw-bold text-uppercase tracking-wider fs-7 shadow">
                                {{ __('ui.confirm') }} <i class="bi bi-check-lg ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
