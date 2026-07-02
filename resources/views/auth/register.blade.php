<x-layout> 
    <div
        class="presto-auth-wrapper min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden py-5 my-5 mb-0">

        <div class="presto-card-glow"></div>

        <div class="container position-relative z-index-2">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">

                    <div class="presto-auth-card p-4 p-sm-5 shadow-lg">

                        <div class="text-center mb-4">
                            <div class="presto-icon-wrapper mb-3">
                                <i class="bi bi-person-plus-fill text-accent-cyan fs-3"></i>
                            </div>
                            <h2 class="fw-black text-white text-uppercase tracking-tight h4 mb-1">
                                {{ __('ui.create_new_account') }} <span class="text-glow text-accent-cyan">{{ __('ui.account') }}</span>
                            </h2>
                            <p class="text-muted-custom fs-7 mb-0">{{ __('ui.join_community') }}</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="name"
                                    class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ __('ui.username') }}</label>
                                <div
                                    class="presto-input-group d-flex align-items-center px-3 {{ $errors->has('name') ? 'is-invalid-group' : '' }}">
                                    <i class="bi bi-person text-white-50 me-2"></i>
                                    <input type="text" name="name" id="name"
                                        class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100"
                                        placeholder="{{ __('ui.name_placeholder') }}" value="{{ old('name') }}" required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback-custom mt-1 animate-fade-in">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email"
                                    class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ __('ui.email_address') }}</label>
                                <div
                                    class="presto-input-group d-flex align-items-center px-3 {{ $errors->has('email') ? 'is-invalid-group' : '' }}">
                                    <i class="bi bi-envelope text-white-50 me-2"></i>
                                    <input type="email" name="email" id="email"
                                        class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100"
                                        placeholder="{{ __('ui.email_placeholder') }}" value="{{ old('email') }}" required>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback-custom mt-1 animate-fade-in">
                                        <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password"
                                    class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ __('ui.password') }}</label>
                                <div
                                    class="presto-input-group d-flex align-items-center px-3 {{ $errors->has('password') ? 'is-invalid-group' : '' }}">
                                    <i class="bi bi-lock text-white-50 me-2"></i>
                                    <input type="password" name="password" id="password"
                                        class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100"
                                        placeholder="{{ __('ui.password_placeholder_register') }}" required>
                                    <button type="button" class="btn btn-toggle-password p-0 border-0 bg-transparent"
                                        onclick="togglePasswordVisibility('password', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback-custom mt-1 animate-fade-in">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation"
                                    class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ __('ui.confirm_password') }}</label>
                                <div class="presto-input-group d-flex align-items-center px-3">
                                    <i class="bi bi-shield-lock text-white-50 me-2"></i>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100"
                                        placeholder="{{ __('ui.password_confirmation_placeholder') }}" required>
                                    <button type="button" class="btn btn-toggle-password p-0 border-0 bg-transparent"
                                        onclick="togglePasswordVisibility('password_confirmation', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit"
                                class="btn btn-info text-dark w-100 rounded-pill py-2 fw-bold text-uppercase tracking-wider fs-7 mb-3 shadow">
                                {{ __('ui.register') }} <i class="bi bi-arrow-right-short ms-1"></i>
                            </button>

                            <div class="text-center">
                                <span class="fs-8 text-white-50">{{ __('ui.already_have_account') }}</span>
                                <a href="{{ route('login') }}"
                                    class="presto-link-login fs-8 text-accent-cyan fw-bold text-decoration-none ms-1 transition-all">
                                    {{ __('ui.login_here') }}
                                </a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
