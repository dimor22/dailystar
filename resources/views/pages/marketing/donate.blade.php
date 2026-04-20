<x-layouts.marketing title="Support DailyStars – Help Homeschool Families">

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- DONATION PAGE                                                           --}}
{{-- Conversion-optimised: social proof · tiered amounts · trust signals    --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}

<div
    x-data="{
        selected: 2500,
        custom: '',
        get amountCents() {
            if (this.selected !== 'custom') return this.selected;
            const dollars = parseFloat(this.custom);
            if (!isNaN(dollars) && dollars >= 1) return Math.round(dollars * 100);
            return 0;
        },
        get amountDisplay() {
            const cents = this.amountCents;
            if (cents < 100) return '';
            return '$' + (cents / 100).toLocaleString('en-US', { minimumFractionDigits: 0 });
        },
        get isReady() { return this.amountCents >= 100 && this.amountCents <= 100000; }
    }"
    class="space-y-8"
>

    {{-- ── Error flash ────────────────────────────────────────────────────── --}}
    @if (session('donation_error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-800">
            {{ session('donation_error') }}
        </div>
    @endif

    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <section class="marketing-panel overflow-hidden p-7 sm:p-12 text-center relative">
        {{-- decorative star burst --}}
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-amber-300/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-20 -bottom-20 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>

        <div class="relative z-10">
            <p class="marketing-eyebrow">Support the Mission</p>
            <h1 class="marketing-h2 mt-3 max-w-2xl mx-auto">
                Help more families build routines kids actually enjoy 🌟
            </h1>
            <p class="marketing-lead mt-4 max-w-xl mx-auto text-slate-600">
                DailyStars is built by a small team for homeschool families. Every donation goes directly to keeping the app running, improving features, and keeping it affordable.
            </p>

            {{-- Social proof strip --}}
            <div class="mt-7 flex flex-wrap items-center justify-center gap-6 text-center">
                <div>
                    <p class="marketing-stat text-3xl">2,000+</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-0.5">Families using DailyStars</p>
                </div>
                <div class="h-10 w-px bg-slate-200 hidden sm:block"></div>
                <div>
                    <p class="marketing-stat text-3xl">100%</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-0.5">One-time · No subscription</p>
                </div>
                <div class="h-10 w-px bg-slate-200 hidden sm:block"></div>
                <div>
                    <p class="marketing-stat text-3xl">🔒</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-0.5">Secured by Stripe</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Donation form ──────────────────────────────────────────────────── --}}
    <section class="marketing-panel p-7 sm:p-10">
        <form method="POST" action="{{ route('donation.checkout') }}">
            @csrf
            <input type="hidden" name="amount_cents" :value="amountCents">

            {{-- Amount heading --}}
            <div class="text-center mb-6">
                <h2 class="marketing-h3">Choose your contribution</h2>
                <p class="marketing-copy text-slate-500 mt-1 text-sm">All amounts are one-time gifts in USD.</p>
            </div>

            {{-- ── Top 5 preset amount buttons ────────────────────────────── --}}
            {{--
                Research-backed top donation amounts for indie SaaS:
                $5 (entry) · $10 (popular) · $25 (most chosen) · $50 (champion) · $100 (hero)
            --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">

                {{-- $5 --}}
                <button
                    type="button"
                    @click="selected = 500; custom = ''"
                    :class="selected === 500
                        ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-300 shadow-lg scale-[1.03]'
                        : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-sky-50/60'"
                    class="relative flex flex-col items-center rounded-2xl border-2 px-4 py-5 transition-all duration-150 cursor-pointer"
                >
                    <span class="text-3xl leading-none">☕</span>
                    <span class="mt-2 text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$5</span>
                    <span class="mt-1 text-xs font-semibold text-slate-500">A coffee</span>
                </button>

                {{-- $10 --}}
                <button
                    type="button"
                    @click="selected = 1000; custom = ''"
                    :class="selected === 1000
                        ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-300 shadow-lg scale-[1.03]'
                        : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-sky-50/60'"
                    class="relative flex flex-col items-center rounded-2xl border-2 px-4 py-5 transition-all duration-150 cursor-pointer"
                >
                    <span class="text-3xl leading-none">🎒</span>
                    <span class="mt-2 text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$10</span>
                    <span class="mt-1 text-xs font-semibold text-slate-500">One week hosted</span>
                </button>

                {{-- $25 — Most popular --}}
                <button
                    type="button"
                    @click="selected = 2500; custom = ''"
                    :class="selected === 2500
                        ? 'border-amber-400 bg-amber-50 ring-2 ring-amber-300 shadow-lg scale-[1.03]'
                        : 'border-amber-300 bg-amber-50/40 hover:border-amber-400 hover:bg-amber-50'"
                    class="relative col-span-2 sm:col-span-1 flex flex-col items-center rounded-2xl border-2 px-4 py-5 transition-all duration-150 cursor-pointer"
                >
                    {{-- Badge --}}
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-amber-400 px-3 py-0.5 text-xs font-extrabold uppercase text-slate-900 shadow">
                        ⭐ Most popular
                    </span>
                    <span class="text-3xl leading-none mt-1">🌟</span>
                    <span class="mt-2 text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$25</span>
                    <span class="mt-1 text-xs font-semibold text-slate-500">A month of features</span>
                </button>

                {{-- $50 --}}
                <button
                    type="button"
                    @click="selected = 5000; custom = ''"
                    :class="selected === 5000
                        ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-300 shadow-lg scale-[1.03]'
                        : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-sky-50/60'"
                    class="relative flex flex-col items-center rounded-2xl border-2 px-4 py-5 transition-all duration-150 cursor-pointer"
                >
                    <span class="text-3xl leading-none">🚀</span>
                    <span class="mt-2 text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$50</span>
                    <span class="mt-1 text-xs font-semibold text-slate-500">Power supporter</span>
                </button>

                {{-- $100 --}}
                <button
                    type="button"
                    @click="selected = 10000; custom = ''"
                    :class="selected === 10000
                        ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-300 shadow-lg scale-[1.03]'
                        : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-sky-50/60'"
                    class="relative flex flex-col items-center rounded-2xl border-2 px-4 py-5 transition-all duration-150 cursor-pointer"
                >
                    <span class="text-3xl leading-none">🏆</span>
                    <span class="mt-2 text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$100</span>
                    <span class="mt-1 text-xs font-semibold text-slate-500">DailyStars Champion</span>
                </button>

            </div>

            {{-- ── Custom amount ───────────────────────────────────────────── --}}
            <div class="mt-5">
                <button
                    type="button"
                    @click="selected = 'custom'"
                    :class="selected === 'custom'
                        ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-300'
                        : 'border-slate-200 bg-white hover:border-sky-300'"
                    class="w-full rounded-2xl border-2 px-5 py-4 text-left transition-all duration-150"
                >
                    <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Or enter a custom amount (USD)</p>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-extrabold text-slate-400">$</span>
                        <input
                            type="number"
                            x-model="custom"
                            @focus="selected = 'custom'"
                            min="1"
                            max="1000"
                            step="1"
                            placeholder="Enter amount"
                            class="flex-1 bg-transparent text-xl font-extrabold text-slate-900 outline-none placeholder-slate-300"
                        >
                    </div>
                </button>
            </div>

            {{-- ── CTA button ──────────────────────────────────────────────── --}}
            <div class="mt-8">
                <button
                    type="submit"
                    :disabled="!isReady"
                    :class="isReady
                        ? 'bg-amber-400 hover:bg-amber-300 text-slate-900 shadow-[0_6px_24px_rgba(245,165,36,0.4)] hover:shadow-[0_8px_30px_rgba(245,165,36,0.5)] scale-100 hover:scale-[1.02]'
                        : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    class="w-full rounded-2xl px-6 py-5 text-xl font-extrabold transition-all duration-150"
                    style="font-family:'Baloo 2',cursive"
                >
                    <span x-show="isReady" x-cloak>
                        Donate <span x-text="amountDisplay"></span> →
                    </span>
                    <span x-show="!isReady">
                        Select an amount above
                    </span>
                    <span x-show="isReady && selected === 2500" x-cloak class="ml-2 text-sm font-bold opacity-70">⭐ Great choice!</span>
                </button>

                {{-- Trust signals --}}
                <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-xs font-semibold text-slate-400">
                    <span class="flex items-center gap-1">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Secured by Stripe
                    </span>
                    <span>·</span>
                    <span>One-time payment</span>
                    <span>·</span>
                    <span>No account required</span>
                    <span>·</span>
                    <span>Cancel anytime isn't needed — it's a gift!</span>
                </div>
            </div>

        </form>
    </section>

    {{-- ── Impact breakdown ───────────────────────────────────────────────── --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

        <article class="marketing-feature-card">
            <div class="flex items-start gap-3">
                <span class="text-3xl shrink-0">🛠️</span>
                <div>
                    <h3 class="font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">Feature Development</h3>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Your gift funds new features parents request most — better reporting, badge customisation, and sibling competitions.</p>
                </div>
            </div>
        </article>

        <article class="marketing-feature-card">
            <div class="flex items-start gap-3">
                <span class="text-3xl shrink-0">🖥️</span>
                <div>
                    <h3 class="font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">Hosting & Reliability</h3>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Servers, backups, CDN, and security keep DailyStars fast and available every morning when kids need it most.</p>
                </div>
            </div>
        </article>

        <article class="marketing-feature-card">
            <div class="flex items-start gap-3">
                <span class="text-3xl shrink-0">❤️</span>
                <div>
                    <h3 class="font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">Keeping It Affordable</h3>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Donations let us keep the free plan generous and the Pro plan cheap, so no family is left out because of cost.</p>
                </div>
            </div>
        </article>

    </section>

    {{-- ── FAQ ────────────────────────────────────────────────────────────── --}}
    <section class="marketing-panel p-7 sm:p-10">
        <h2 class="marketing-h3 text-center">Quick questions</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div>
                <p class="font-extrabold text-slate-900">Is my donation tax-deductible?</p>
                <p class="mt-1 text-sm font-semibold text-slate-600">DailyStars is not a registered non-profit, so donations are not tax-deductible. They're a direct gift to support an indie product you love.</p>
            </div>
            <div>
                <p class="font-extrabold text-slate-900">Is this a recurring charge?</p>
                <p class="mt-1 text-sm font-semibold text-slate-600">No. Every donation is a one-time payment. You will never be charged again unless you choose to donate again.</p>
            </div>
            <div>
                <p class="font-extrabold text-slate-900">Do I need a DailyStars account?</p>
                <p class="mt-1 text-sm font-semibold text-slate-600">No account required. Anyone who wants to support the mission can donate, even if they've never used the app.</p>
            </div>
            <div>
                <p class="font-extrabold text-slate-900">How is my payment processed?</p>
                <p class="mt-1 text-sm font-semibold text-slate-600">Payments are handled by Stripe, one of the world's most trusted payment processors. We never see your card details.</p>
            </div>
        </div>
    </section>

</div>
</x-layouts.marketing>
