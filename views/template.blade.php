<html lang="{{ $g_page_lang }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta property="og:title" content="{{ $g_page_title }}">
        <meta property="og:description" content="{{ $g_page_description }}">
        <meta property="og:url" content="{{ $g_page_url }}">
        <meta name="twitter:card" content="summary_large_image">

        <title>{{ $g_page_title }}</title>

        <!-- Favicon and Icons -->
        <link rel="icon" type="image/png" sizes="16x16" href="/resources/images/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/resources/images/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/resources/images/android-chrome-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/resources/images/android-chrome-512x512.png">
        <link rel="apple-touch-icon" href="/resources/images/apple-touch-icon.png">
        <link rel="shortcut icon" href="favicon.ico">

        <!-- Utilities -->
        <link rel="stylesheet" href="build/main.css" />
        <link rel="stylesheet" href="build/utilities.css" />
        <script src="build/main.js"></script>
    </head>
    <body>
        {!! $g_page_content !!}
        <noscript>
            <p>JavaScript is required for this website to function properly.</p>
        </noscript>
    </body>
</html>