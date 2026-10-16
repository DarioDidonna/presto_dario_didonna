{{-- Parere dell'AI per il revisore: solo un suggerimento, la decisione resta umana --}}
<div class="p-3 rounded border border-secondary bg-black mt-3">
    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <span class="text-neon-cyan font-monospace small text-uppercase">
            <i class="bi bi-robot me-1" aria-hidden="true"></i>{{ __('ui.review_ai_title') }}
        </span>
        @unless ($suggestion)
            <button type="button" wire:click="ask" wire:loading.attr="disabled" wire:target="ask" class="btn btn-sm btn-outline-info">
                <span wire:loading.remove wire:target="ask"><i class="bi bi-stars me-1" aria-hidden="true"></i>{{ __('ui.review_ai_ask') }}</span>
                <span wire:loading wire:target="ask"><span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>{{ __('ui.review_ai_thinking') }}</span>
            </button>
        @endunless
    </div>

    @if ($unavailable)
        <p class="text-warning fs-7 mb-0 mt-2" role="status">{{ __('ui.assistant_unavailable') }}</p>
    @endif

    @if ($suggestion)
        @php
            $badge = [
                'accept' => ['bg-success', 'bi-check-circle', __('ui.review_ai_accept')],
                'reject' => ['bg-danger', 'bi-x-octagon', __('ui.review_ai_reject')],
                'unsure' => ['bg-warning text-dark', 'bi-question-circle', __('ui.review_ai_unsure')],
            ][$suggestion['recommendation']];
        @endphp
        <div class="mt-2" role="status">
            <span class="badge {{ $badge[0] }} fs-7">
                <i class="bi {{ $badge[1] }} me-1" aria-hidden="true"></i>{{ $badge[2] }}
            </span>
            @if ($suggestion['model_used'])
                <span class="text-muted-custom fs-8 ms-2">{{ __('ui.review_ai_confidence', ['value' => round($suggestion['confidence'] * 100)]) }}</span>
            @else
                <span class="text-muted-custom fs-8 ms-2">{{ __('ui.review_ai_rules_only') }}</span>
            @endif

            @if ($suggestion['flags'])
                <ul class="list-unstyled mt-2 mb-0 fs-7">
                    @foreach ($suggestion['flags'] as $flag)
                        <li class="{{ $flag['blocking'] ? 'text-danger' : 'text-warning' }}">
                            <i class="bi {{ $flag['blocking'] ? 'bi-slash-circle' : 'bi-exclamation-triangle' }} me-1" aria-hidden="true"></i>
                            {{ trans()->has('ui.review_flag_'.$flag['code']) ? __('ui.review_flag_'.$flag['code']) : str_replace('_', ' ', $flag['code']) }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($suggestion['reasons'])
                <ul class="text-light fs-7 mt-2 mb-0 ps-3">
                    @foreach ($suggestion['reasons'] as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            @endif

            <p class="text-muted-custom fs-8 fst-italic mt-2 mb-0">{{ __('ui.review_ai_disclaimer') }}</p>
        </div>
    @endif
</div>
