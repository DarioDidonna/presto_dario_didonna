{{-- Assistente all'acquisto: pulsante flottante + finestra di chat --}}
<div>
    <button type="button" wire:click="toggle"
        class="btn btn-cyber-gradient rounded-circle shadow-lg assistant-toggle d-flex align-items-center justify-content-center"
        aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="assistantPanel"
        title="{{ __('ui.assistant_title') }}">
        <i class="bi {{ $open ? 'bi-x-lg' : 'bi-chat-dots-fill' }} fs-4" aria-hidden="true"></i>
        <span class="visually-hidden">{{ $open ? __('ui.close') : __('ui.assistant_open') }}</span>
    </button>

    @if ($open)
        <section id="assistantPanel" class="assistant-panel bg-navbar-card border border-neon-cyan rounded-4 shadow-lg d-flex flex-column"
            aria-label="{{ __('ui.assistant_title') }}">

            <header class="d-flex align-items-center justify-content-between gap-2 p-3 border-bottom border-secondary-subtle">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-robot text-neon-cyan fs-5" aria-hidden="true"></i>
                    <div>
                        <h2 class="h6 text-white fw-bold mb-0">{{ __('ui.assistant_title') }}</h2>
                        <small class="text-muted-custom fs-8">{{ __('ui.assistant_subtitle') }}</small>
                    </div>
                </div>
                @if ($messages)
                    <button type="button" wire:click="resetConversation" class="btn btn-sm btn-outline-secondary text-white"
                        title="{{ __('ui.assistant_reset') }}">
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        <span class="visually-hidden">{{ __('ui.assistant_reset') }}</span>
                    </button>
                @endif
            </header>

            {{-- Scorre da solo all'ultimo messaggio quando Livewire aggiorna la chat --}}
            <div class="assistant-messages flex-grow-1 overflow-auto p-3 d-flex flex-column gap-3" role="log" aria-live="polite"
                x-data x-init="$el.scrollTop = $el.scrollHeight;
                    new MutationObserver(() => $el.scrollTop = $el.scrollHeight).observe($el, { childList: true, subtree: true })">
                @if (! $messages)
                    <div class="text-muted-custom fs-7">
                        <p class="mb-2">{{ __('ui.assistant_welcome') }}</p>
                        <p class="mb-0 fst-italic">{{ __('ui.assistant_example') }}</p>
                    </div>
                @endif

                @foreach ($messages as $index => $message)
                    @php($bubble = ['user' => 'assistant-user align-self-end', 'assistant' => 'assistant-assistant align-self-start', 'error' => 'assistant-error align-self-start'][$message['role']])
                    <div wire:key="assistant-message-{{ $index }}" class="assistant-bubble {{ $bubble }}">
                        <p class="mb-0 text-pre-wrap">{{ $message['text'] }}</p>

                        @foreach ($message['articles'] ?? [] as $card)
                            <div class="assistant-card d-flex align-items-center gap-2 mt-2 p-2 rounded">
                                <a href="{{ $card['url'] }}" class="d-flex align-items-center gap-2 text-decoration-none flex-grow-1 min-w-0">
                                    <img src="{{ $card['image'] }}" alt="" width="48" height="48" loading="lazy" class="rounded object-fit-cover flex-shrink-0">
                                    <span class="min-w-0">
                                        <span class="d-block text-white fs-7 fw-semibold text-truncate">{{ $card['title'] }}</span>
                                        <span class="d-block text-neon-cyan fs-8 font-monospace">{{ $card['price'] }} €</span>
                                    </span>
                                </a>
                                @isset($card['id'])
                                    @auth
                                        @if (in_array($card['id'], $cartIds, true))
                                            <a href="{{ route('cart.index') }}" class="btn btn-sm btn-success flex-shrink-0" title="{{ __('ui.cart_in') }}">
                                                <i class="bi bi-bag-check-fill" aria-hidden="true"></i><span class="visually-hidden">{{ __('ui.cart_in') }}</span>
                                            </a>
                                        @else
                                            <button type="button" wire:click="addToCart({{ $card['id'] }})" class="btn btn-sm btn-warning flex-shrink-0"
                                                title="{{ __('ui.cart_add') }}" wire:loading.attr="disabled" wire:target="addToCart({{ $card['id'] }})">
                                                <i class="bi bi-bag-plus-fill" aria-hidden="true"></i><span class="visually-hidden">{{ __('ui.cart_add') }}: {{ $card['title'] }}</span>
                                            </button>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-warning flex-shrink-0" title="{{ __('ui.assistant_login_to_buy') }}">
                                            <i class="bi bi-bag-plus" aria-hidden="true"></i><span class="visually-hidden">{{ __('ui.assistant_login_to_buy') }}</span>
                                        </a>
                                    @endauth
                                @endisset
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div wire:loading wire:target="send" class="assistant-bubble assistant-assistant align-self-start text-muted-custom fs-7" role="status">
                    <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> {{ __('ui.assistant_thinking') }}
                </div>
            </div>

            <form wire:submit="send" class="p-3 border-top border-secondary-subtle">
                <div class="input-group">
                    <label for="assistantInput" class="visually-hidden">{{ __('ui.assistant_placeholder') }}</label>
                    <input type="text" id="assistantInput" wire:model="input" maxlength="500" autocomplete="off"
                        placeholder="{{ __('ui.assistant_placeholder') }}"
                        class="form-control bg-dark border-secondary text-white @error('input') is-invalid @enderror">
                    <button type="submit" class="btn btn-cyber-gradient" wire:loading.attr="disabled" wire:target="send"
                        aria-label="{{ __('ui.assistant_send') }}">
                        <i class="bi bi-send-fill" aria-hidden="true"></i>
                    </button>
                </div>
                @error('input')
                    <div class="text-danger fs-8 mt-1">{{ $message }}</div>
                @enderror
                @error('cart')
                    <div class="text-warning fs-8 mt-1">{{ $message }}</div>
                @enderror
                <p class="text-muted-custom fs-9 mb-0 mt-2">{{ __('ui.assistant_disclaimer') }}</p>
            </form>
        </section>
    @endif
</div>
