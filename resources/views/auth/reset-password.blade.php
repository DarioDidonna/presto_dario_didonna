<x-layout :title="__('ui.reset_password_title')">
    <div class="presto-auth-wrapper min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden pt-custom-navbar pb-5">

        <div class="presto-card-glow"></div>

        <div class="container position-relative z-index-2">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                    <div class="presto-auth-card p-4 p-sm-5 shadow-lg">
                        <div class="text-center mb-4">
                            <div class="presto-icon-wrapper mb-3">
                                <i class="bi bi-shield-lock-fill text-accent-cyan fs-3"></i>
                            </div>
                            <h1 class="fw-black text-white text-uppercase tracking-tight h4 mb-1">{{ __('ui.reset_password_title') }}</h1>
                            <p class="text-muted-custom fs-7 mb-0">{{ __('ui.reset_password_subtitle') }}</p>
                        </div>

                        <form method="POST" action="{{ route('password.update') }}" novalidate>
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="mb-3">
                                <label for="email" class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ __('ui.email_address') }}</label>
                                <div class="presto-input-group d-flex align-items-center px-3 {{ $errors->has('email') ? 'is-invalid-group' : '' }}">
                                    <i class="bi bi-envelope text-neon-cyan me-2"></i>
                                    <input type="email" name="email" id="email" value="{{ old('email', $request->email) }}" required autocomplete="email"
                                        class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback-custom mt-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>

                            @foreach (['password' => __('ui.new_password'), 'password_confirmation' => __('ui.confirm_password')] as $field => $label)
                                <div class="{{ $loop->last ? 'mb-4' : 'mb-3' }}">
                                    <label for="{{ $field }}" class="form-label text-white-50 fs-7 text-uppercase tracking-wider fw-bold">{{ $label }}</label>
                                    <div class="presto-input-group d-flex align-items-center px-3 {{ $errors->has($field) ? 'is-invalid-group' : '' }}">
                                        <i class="bi bi-lock text-neon-cyan me-2"></i>
                                        <input type="password" name="{{ $field }}" id="{{ $field }}" required autocomplete="new-password"
                                            @if ($loop->first) autofocus @endif
                                            class="form-control bg-presto-input presto-input text-white border-0 py-2-5 fs-7 w-100">
                                        <button type="button" class="btn btn-toggle-password p-0 border-0 bg-transparent"
                                            data-toggle-password="{{ $field }}" aria-label="{{ __('ui.show_password') }}" aria-pressed="false">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </div>
                                    @error($field)
                                        <div class="invalid-feedback-custom mt-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach

                            <button type="submit" class="btn btn-info text-dark w-100 rounded-pill py-2 fw-bold text-uppercase tracking-wider fs-7 shadow">
                                {{ __('ui.reset_password_button') }} <i class="bi bi-check-lg ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
