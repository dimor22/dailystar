<x-layouts.marketing title="Thank You – DailyStars">

<div class="flex flex-col items-center justify-center py-10 text-center space-y-6">

    {{-- Confetti / celebration icon --}}
    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-amber-100 text-6xl shadow-lg">
        🌟
    </div>

    <div>
        <p class="marketing-eyebrow">You're amazing</p>
        <h1 class="marketing-h2 mt-3 max-w-lg mx-auto">
            Thank you for supporting DailyStars!
        </h1>
        <p class="marketing-lead mt-4 max-w-md mx-auto text-slate-600">
            Your generosity helps us keep the app running and improving for homeschool families everywhere. It means the world to us. 💛
        </p>
    </div>

    {{-- What happens next --}}
    <div class="marketing-panel p-7 sm:p-10 w-full max-w-lg text-left space-y-4">
        <h2 class="marketing-h3 text-center">What happens next</h2>
        <ul class="mt-4 space-y-3 text-sm font-semibold text-slate-700">
            <li class="flex items-start gap-3">
                <span class="shrink-0 text-emerald-500 text-lg">✓</span>
                <span>You'll receive a receipt from Stripe at the email you used at checkout.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="shrink-0 text-emerald-500 text-lg">✓</span>
                <span>Your one-time gift goes directly toward hosting, development, and keeping DailyStars affordable.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="shrink-0 text-emerald-500 text-lg">✓</span>
                <span>You will <strong>not</strong> be charged again — this was a one-time donation.</span>
            </li>
        </ul>
    </div>

    {{-- CTAs --}}
    <div class="flex flex-wrap items-center justify-center gap-4">
        <a href="{{ route('marketing.home') }}" class="marketing-btn marketing-btn-primary px-6 py-3 text-base">
            Back to Home
        </a>
        <a href="{{ route('parent.login') }}" class="marketing-btn marketing-btn-outline px-6 py-3 text-base">
            Parent Login
        </a>
    </div>

    <p class="text-xs font-semibold text-slate-400">
        Questions? <a href="{{ route('marketing.contact') }}" class="underline hover:text-slate-600">Contact us</a> — we'd love to hear from you.
    </p>

</div>

</x-layouts.marketing>
