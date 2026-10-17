<section class="conversation-box rounded-3 border border-secondary-subtle bg-navbar-card shadow-lg" aria-label="{{ __('ui.messages_title') }}">
    <div class="conversation-messages p-3 p-md-4 d-flex flex-column gap-3" role="log" aria-live="polite"
        x-data x-init="$el.scrollTop = $el.scrollHeight" @conversation-updated.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
        @forelse ($messages as $message)
            @php($mine = $message->user_id === auth()->id())
            <div class="d-flex flex-column {{ $mine ? 'align-items-end' : 'align-items-start' }}" wire:key="message-{{ $message->id }}">
                <div class="message-bubble {{ $mine ? 'message-mine' : 'message-theirs' }} px-3 py-2 rounded-3">{{ $message->body }}</div>
                <span class="text-muted-custom fs-8 mt-1">
                    {{ $mine ? __('ui.messages_you') : $message->user?->name ?? __('ui.anonymous_user') }}
                    · <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->translatedFormat('j M, H:i') }}</time>
                </span>
            </div>
        @empty
            <div class="text-center text-muted-custom fs-7 py-5 my-auto">
                <i class="bi bi-chat-dots fs-2 text-neon-cyan d-block mb-2" aria-hidden="true"></i>
                {{ __('ui.messages_empty') }}
            </div>
        @endforelse
    </div>

    <div class="border-top border-secondary-subtle p-3">
        @if ($canWrite)
            <form wire:submit="send" class="d-flex gap-2 align-items-end">
                <label for="conversation-body" class="visually-hidden">{{ __('ui.messages_placeholder') }}</label>
                <textarea id="conversation-body" wire:model="body" rows="2" maxlength="1000"
                    class="form-control conversation-input fs-7 @error('body') is-invalid @enderror"
                    placeholder="{{ __('ui.messages_placeholder') }}"
                    @keydown.enter="if (!$event.shiftKey && !$event.isComposing) { $event.preventDefault(); $wire.send() }"></textarea>
                <button type="submit" class="btn btn-cyber-gradient text-light flex-shrink-0" wire:loading.attr="disabled" wire:target="send">
                    <i class="bi bi-send-fill" aria-hidden="true"></i><span class="visually-hidden">{{ __('ui.messages_send') }}</span>
                </button>
            </form>
            @error('body')
                <div class="text-danger fs-8 mt-1">{{ $message }}</div>
            @enderror
            <div class="text-muted-custom fs-8 mt-1">{{ __('ui.messages_enter_hint') }}</div>
        @else
            <p class="text-muted-custom fs-7 mb-0">
                <i class="bi bi-lock me-1" aria-hidden="true"></i>{{ __('ui.messages_closed_'.$status) }}
            </p>
        @endif
    </div>
</section>
