@props(['title' => null, 'description' => null, 'image' => null, 'imageAlt' => null, 'type' => 'website'])
@php
    $pageTitle = $title ? trim($title) . ' · Presto.it' : 'Presto.it - ' . __('ui.meta_tagline');
    $pageDescription = $description ?? __('ui.meta_description');
    // Anteprima nei link condivisi: immagine 1200x630 predefinita, oppure quella passata dalla pagina (es. foto dell'annuncio)
    $shareImage = $image ? url($image) : asset('media/og-default.jpg');
    $ogLocales = ['it' => 'it_IT', 'en' => 'en_GB', 'es' => 'es_ES'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph (WhatsApp, Facebook, LinkedIn, Telegram) e Twitter/X --}}
    <meta property="og:site_name" content="Presto.it">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] ?? 'it_IT' }}">
    <meta property="og:image" content="{{ $shareImage }}">
    @unless ($image)
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endunless
    <meta property="og:image:alt" content="{{ $imageAlt ?? 'Presto.it' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    {{ $meta ?? '' }}
    <link rel="icon" type="image/png" href="{{ asset('/media/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ $pageTitle }}</title>
</head>
<body>

    <a href="#main-content" class="visually-hidden-focusable btn btn-info skip-link">
        {{ __('ui.skip_to_content') }}
    </a>

    <x-navbar />

    <main id="main-content" class="min-vh-100">
        {{ $slot }}
    </main>

    <x-footer />

    {{-- Assistente AI: compare solo se l'agente è configurato (AI_AGENT_TOKEN) --}}
    @if (app(\App\Services\AiAgentClient::class)->isEnabled())
        <livewire:shopping-assistant />
    @endif

</body>
</html>
