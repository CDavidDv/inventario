<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @if (app()->environment('local'))
            <script type="module" src="http://localhost:5173/@vite/client"></script>
            <script type="module" src="http://localhost:5173/resources/js/app.js"></script>
            <link rel="stylesheet" href="http://localhost:5173/resources/css/app.css">
        @else
            @php
                $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
                $cssFile = $manifest['resources/css/app.css']['file'] ?? 'assets/app.css';
                $jsFile = $manifest['resources/js/app.js']['file'] ?? 'assets/app.js';
                $appCss = $manifest['resources/js/app.js']['css'] ?? [];
            @endphp
            <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
            @foreach($appCss as $css)
                <link rel="stylesheet" href="{{ asset('build/' . $css) }}">
            @endforeach
            <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
        @endif
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
