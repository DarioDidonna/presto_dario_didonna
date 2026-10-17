@props(['box', 'count' => 0])
{{-- Messaggi non letti di una voce del menu; resources/js/echo.js lo incrementa quando ne arriva uno nuovo --}}
<span class="badge bg-info text-dark rounded-pill" data-unread-badge="{{ $box }}" title="{{ __('ui.messages_unread') }}"
    @if ($count === 0) hidden @endif><i class="bi bi-chat-dots-fill me-1" aria-hidden="true"></i><span data-unread-count>{{ $count }}</span><span class="visually-hidden"> {{ __('ui.messages_unread') }}</span></span>
