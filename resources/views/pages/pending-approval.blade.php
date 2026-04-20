<x-layouts.app :title="'Account Pending Approval'">
    <div class="flex min-h-[70vh] items-center justify-center px-4 py-16">
        <div class="mx-auto w-full max-w-md text-center">

            {{-- Illustration --}}
            <div class="mb-6 flex justify-center">
                <div class="flex h-24 w-24 items-center justify-center rounded-full bg-amber-100 text-5xl shadow-md">
                    ⏳
                </div>
            </div>

            {{-- Heading --}}
            <h1 class="mb-3 text-3xl font-extrabold tracking-tight text-slate-900" style="font-family:'Baloo 2',cursive">
                You're on the list!
            </h1>

            {{-- Body copy --}}
            <p class="mb-2 text-lg font-semibold text-slate-600">
                Your account is currently <span class="text-amber-600">pending approval</span>.
            </p>
            <p class="mb-8 text-base text-slate-500">
                We review every new account to keep DailyStars a safe and welcoming place for families.
                You'll be able to sign in as soon as an admin approves your registration — usually within
                <strong class="text-slate-700">one business day</strong>.
            </p>

            {{-- Soft card with what to expect --}}
            <div class="mb-8 rounded-2xl border border-amber-100 bg-amber-50 px-6 py-5 text-left shadow-sm">
                <p class="mb-3 text-sm font-extrabold uppercase tracking-wider text-amber-700">What happens next?</p>
                <ul class="space-y-2 text-sm font-semibold text-slate-600">
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 text-amber-500">✔</span>
                        Your account has been created successfully.
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 text-amber-500">✔</span>
                        An admin will review and approve your registration.
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 text-amber-500">✔</span>
                        Once approved, you can sign in and start using DailyStars.
                    </li>
                </ul>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col items-center gap-3">
                <a href="{{ route('parent.login') }}" class="kid-btn kid-btn-primary w-full max-w-xs">
                    Back to Sign In
                </a>
                <a href="{{ route('marketing.home') }}" class="text-sm font-semibold text-slate-400 hover:text-slate-600 transition-colors">
                    Go to homepage
                </a>
            </div>

        </div>
    </div>
</x-layouts.app>
