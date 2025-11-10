<html lang="{{ env('APP_LANGUAGE') }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta property="og:title" content="{{ env('APP_NAME') }}">
        <meta property="og:description" content="Page description here">
        <meta property="og:url" content="{{ env('APP_URL') }}">
        <meta name="twitter:card" content="summary_large_image">

        <title>{{ env('APP_NAME') }}</title>

        <!-- Favicon and Icons -->
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset("/resources/images/favicon-16x16.png") }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset("/resources/images/favicon-32x32.png") }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset("/resources/images/android-chrome-192x192.png") }}">
        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset("/resources/images/android-chrome-512x512.png") }}">
        <link rel="apple-touch-icon" href="{{ asset("/resources/images/apple-touch-icon.png") }}">
        <link rel="shortcut icon" href="favicon.ico">

        <!-- Utilities -->
        <link rel="stylesheet" href="{{ asset("/build/main.css") }}" />
        <link rel="stylesheet" href="{{ asset("/build/utilities.css") }}" />
        <script src="{{ asset("/build/main.js") }}"></script>
    </head>
    <body>
        @yield('content')
        <noscript>
            <p>JavaScript is required for this website to function properly.</p>
        </noscript>
    </body>
</html>
