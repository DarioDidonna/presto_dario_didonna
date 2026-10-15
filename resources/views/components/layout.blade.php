@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="{{ $description ?? __('ui.meta_description') }}">
    <link rel="icon" type="image/png" href="{{ asset('/media/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ $title ? trim($title) . ' · Presto.it' : 'Presto.it - ' . __('ui.meta_tagline') }}</title>
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

</body>
</html>
