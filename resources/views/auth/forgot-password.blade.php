<x-layout :title="__('ui.forgot_password_title')">
    <div class="presto-auth-wrapper min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden pt-custom-navbar pb-5">

        <div class="presto-card-glow"></div>

        <div class="container position-relative z-index-2">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">

                    @if (session('status'))
                        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success rounded mb-4 fs-7 p-3" role="status">
                            <i class="bi bi-envelope-check-fill me-2"></i> {{ session('status') }}
                        </div>
                    @endif

                    <div class="presto-auth-card p-4 p-sm-5 shadow-lg">
                        <div class="text-center mb-4">
                            <div class="presto-icon-wrapper mb-3">
                                <i class="bi bi-key-fill text-accent-cyan fs-3"></i>
                            </div>
                            <h1 class="fw-black text-white text-uppercase tracking-tight h4 mb-1">{{ __('ui.forgot_password_title') }}</h1>
                            <p class="text-muted-custom fs-7 mb-0">{{ __('ui.forgot_password_subtitle') }}</p>
                        </div>

                        <form method="POST" action="{{ route('password.email') }}" novalidate>
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ __('ui.email_address') }}</label>
                                <div class="presto-input-group d-flex align-items-center px-3 {{ $errors->has('email') ? 'is-invalid-group' : '' }}">
                                    <i class="bi bi-envelope text-neon-cyan me-2"></i>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                                        class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100"
                                        placeholder="{{ __('ui.email_placeholder') }}">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback-custom mt-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-info text-dark w-100 rounded-pill py-2 fw-bold text-uppercase tracking-wider fs-7 mb-3 shadow">
                                {{ __('ui.send_reset_link') }} <i class="bi bi-send ms-1"></i>
                            </button>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="presto-link-login fs-8 text-accent-cyan fw-bold text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i>{{ __('ui.back_to_login') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
