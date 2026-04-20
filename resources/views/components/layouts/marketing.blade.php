<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'DailyStars' }}</title>
    <meta name="description" content="Turn your homeschool day into a game kids want to finish.">
    <link rel="icon" type="image/png" href="{{ asset('dailystars-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700;800&family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing-body min-h-screen">
    <div class="marketing-bg"></div>

    <header class="relative z-20 border-b border-sky-200/70 bg-white/85 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('marketing.home') }}" class="flex items-center gap-3">
                <x-site-logo class="h-10 w-36 sm:h-11 sm:w-40" />
            </a>

            <nav class="flex items-center gap-2 sm:gap-3">
                <a
                    href="{{ route('marketing.home') }}"
                    class="marketing-nav-link {{ request()->routeIs('marketing.home') ? 'bg-sky-200 text-slate-900 ring-1 ring-sky-300' : '' }}"
                >
                    Home
                </a>
                <a
                    href="{{ route('marketing.about') }}"
                    class="marketing-nav-link {{ request()->routeIs('marketing.about') ? 'bg-sky-200 text-slate-900 ring-1 ring-sky-300' : '' }}"
                >
                    About
                </a>
                <a
                    href="{{ route('marketing.contact') }}"
                    class="marketing-nav-link {{ request()->routeIs('marketing.contact', 'marketing.contact.submit') ? 'bg-sky-200 text-slate-900 ring-1 ring-sky-300' : '' }}"
                >
                    Contact
                </a>
                <a href="{{ route('marketing.donate') }}" class="marketing-btn marketing-btn-accent">Donate</a>
                <a href="{{ route('parent.login') }}" class="marketing-btn marketing-btn-primary">Parent Login</a>
            </nav>
        </div>
    </header>

    <x-beta-notice />

    <main class="relative z-10 mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="relative z-20 mt-12 border-t border-sky-200/70 bg-white/85 backdrop-blur">
        <div class="mx-auto grid w-full max-w-6xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div>
                <h3 class="marketing-footer-title">DailyStars</h3>
                <p class="marketing-footer-copy mt-2">Built for homeschool families who want calmer routines, motivated kids, and less daily nagging.</p>
            </div>

            <div>
                <h3 class="marketing-footer-title">Explore</h3>
                <div class="mt-2 flex flex-col gap-1">
                    <a href="{{ route('marketing.terms') }}" class="marketing-footer-link">Terms and Conditions</a>
                    <a href="{{ route('marketing.privacy') }}" class="marketing-footer-link">Privacy Policy</a>
                    <a href="{{ route('marketing.contact') }}" class="marketing-footer-link">Contact</a>
                    <a href="{{ route('marketing.donate') }}" class="marketing-footer-link">Donation Page</a>
                </div>
            </div>

            <div>
                <h3 class="marketing-footer-title">Follow and Support</h3>
                <div class="mt-2 flex flex-col gap-2">
                    <a href="{{ config('services.marketing.tiktok_url') }}" target="_blank" rel="noopener noreferrer" class="marketing-footer-link">TikTok</a>
                    <a href="{{ config('services.marketing.youtube_url') }}" target="_blank" rel="noopener noreferrer" class="marketing-footer-link">YouTube</a>
                    <a href="{{ route('marketing.donate') }}" class="marketing-btn marketing-btn-accent inline-flex w-fit">Donate to DailyStars</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
