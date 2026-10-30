<x-layout :title="__('ui.verify_email_title')">
    <div class="min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden pt-custom-navbar pb-5">

        <div class="presto-card-glow"></div>

        <div class="container position-relative z-index-2">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success rounded mb-4 fs-7 p-3" role="status">
                            <i class="bi bi-envelope-check-fill me-2"></i> {{ __('ui.verify_email_sent') }}
                        </div>
                    @endif

                    <div class="presto-auth-card p-4 p-sm-5 shadow-lg text-center">
                        <div class="presto-icon-wrapper mb-3">
                            <i class="bi bi-envelope-check text-accent-cyan fs-3"></i>
                        </div>
                        <h1 class="fw-black text-white text-uppercase tracking-tight h4 mb-2">{{ __('ui.verify_email_title') }}</h1>
                        <p class="text-muted-custom fs-7 mb-4">{{ __('ui.verify_email_body') }}</p>

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-info text-dark w-100 rounded-pill py-2 fw-bold text-uppercase">
                                {{ __('ui.verify_email_resend') }} <i class="bi bi-send ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
