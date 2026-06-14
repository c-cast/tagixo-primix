<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="ltr"
    class="h-full"
>
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $title ?? 'Visual Builder' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        :root { --font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        body { font-family: var(--font-family); }
    </style>

    <script>
        (function() {
            const theme = localStorage.getItem('theme') ?? 'system';
            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('vendor/tagixo/tagixo.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tagixo/builder-vendor.css') }}">

    @stack('styles')
</head>

<body class="h-full bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
    {{ $slot }}

    @livewireScripts

    {{-- Stable filename; no ?v= query on the module script (would fork the ES
         module graph → duplicate Pinia / "reading '_s'" crash). --}}
    <script type="module" src="{{ asset('vendor/tagixo/builder.js') }}"></script>

    @stack('scripts')
</body>
</html>
