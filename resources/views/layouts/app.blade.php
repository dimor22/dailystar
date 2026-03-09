<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DailyStars</title>
    <link rel="icon" type="image/png" href="{{ asset('daily-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-sky-100">
    <main class="mx-auto max-w-6xl p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
