<x-layouts.marketing title="Pricing | DailyStars">

    {{-- ═══════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════ --}}
    <section class="marketing-hero overflow-hidden rounded-3xl px-6 py-14 text-center sm:px-10 sm:py-20">
        <p class="marketing-eyebrow">Simple, Honest Pricing</p>
        <h1 class="marketing-h1 mx-auto mt-3 max-w-3xl">
            Turn your homeschool day into something your kids actually finish
        </h1>
        <p class="marketing-lead mx-auto mt-4 max-w-2xl">
            DailyStars helps your kids complete their schoolwork with simple routines, rewards, and motivation that actually works.
        </p>
        <div class="mt-8">
            <a href="{{ route('parent.register') }}"
               class="marketing-btn marketing-btn-primary inline-block px-8 py-3 text-base">
                Start Free
            </a>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════
         PRICING CARDS
    ═══════════════════════════════════════════════════════ --}}
    <section class="mt-14" id="pricing">
        <div class="mx-auto max-w-4xl">
            <div class="grid gap-6 md:grid-cols-2 md:items-stretch">

                {{-- FREE PLAN --}}
                <div class="marketing-panel flex flex-col p-8">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">⭐</span>
                        <h2 class="marketing-h3 text-slate-800">Free</h2>
                    </div>

                    <div class="mt-4">
                        <span class="text-5xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$0</span>
                        <span class="ml-1 text-base font-semibold text-slate-500">/month</span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Free forever — no card required.</p>

                    <ul class="mt-6 flex-1 space-y-3">
                        <x-pricing-feature>Up to 2 kids</x-pricing-feature>
                        <x-pricing-feature>Basic task tracking</x-pricing-feature>
                        <x-pricing-feature>Simple star rewards</x-pricing-feature>
                        <x-pricing-feature>Daily progress view</x-pricing-feature>
                    </ul>

                    <div class="mt-8">
                        <a href="{{ route('parent.register') }}"
                           class="marketing-btn marketing-btn-outline block w-full py-3 text-center text-base">
                            Start Free
                        </a>
                    </div>
                </div>

                {{-- PRO PLAN --}}
                <div class="relative flex flex-col rounded-3xl border-2 border-sky-500 bg-white p-8 shadow-2xl shadow-sky-200/60">

                    {{-- Most Popular badge --}}
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-4 py-1.5 text-xs font-extrabold uppercase tracking-widest text-slate-900 shadow-md">
                            ⭐ Most Popular
                        </span>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <span class="text-2xl">🌟</span>
                        <h2 class="marketing-h3 text-sky-700">Pro</h2>
                    </div>

                    {{-- Billing toggle --}}
                    <div class="mt-4" x-data="{ annual: false }">
                        <div class="flex items-end gap-3">
                            <div>
                                <span class="text-5xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive"
                                      x-text="annual ? '$96' : '$12'"></span>
                                <span class="ml-1 text-base font-semibold text-slate-500"
                                      x-text="annual ? '/year' : '/month'"></span>
                            </div>
                            <span x-show="annual"
                                  x-cloak
                                  class="mb-1 inline-block rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-extrabold text-emerald-700">
                                Save $48/yr
                            </span>
                        </div>

                        {{-- Toggle --}}
                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-sm font-bold" :class="annual ? 'text-slate-400' : 'text-slate-800'">Monthly</span>
                            <button
                                type="button"
                                @click="annual = !annual"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                                :class="annual ? 'bg-sky-600' : 'bg-slate-300'"
                                role="switch"
                                :aria-checked="annual.toString()"
                                aria-label="Toggle annual billing">
                                <span class="pointer-events-none inline-block h-5 w-5 translate-x-0 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                      :class="annual ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                            <span class="text-sm font-bold" :class="annual ? 'text-slate-800' : 'text-slate-400'">Annual</span>
                        </div>
                    </div>

                    <ul class="mt-6 flex-1 space-y-3">
                        <x-pricing-feature accent>Unlimited kids</x-pricing-feature>
                        <x-pricing-feature accent>Unlimited tasks</x-pricing-feature>
                        <x-pricing-feature accent>Points + rewards system</x-pricing-feature>
                        <x-pricing-feature accent>Streak bonuses</x-pricing-feature>
                        <x-pricing-feature accent>Parent dashboard insights</x-pricing-feature>
                        <x-pricing-feature accent>Celebration animations</x-pricing-feature>
                        <x-pricing-feature accent>Priority updates</x-pricing-feature>
                    </ul>

                    <div class="mt-8">
                        <a href="{{ route('parent.register') }}"
                           class="marketing-btn marketing-btn-primary block w-full py-3 text-center text-base shadow-lg shadow-sky-300/50 transition hover:scale-[1.02]">
                            Start Free Trial
                        </a>
                        <p class="mt-3 text-center text-xs font-semibold text-slate-500">
                            7-day free trial. Cancel anytime.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════
         VALUE SECTION
    ═══════════════════════════════════════════════════════ --}}
    <section class="mt-16 text-center">
        <div class="mx-auto max-w-2xl rounded-3xl bg-amber-50 px-8 py-10 ring-1 ring-amber-200">
            <p class="text-3xl font-extrabold leading-snug text-slate-900" style="font-family:'Baloo 2',cursive">
                ⏱️ If this saves you just 10 minutes a day,<br class="hidden sm:block">
                that's <span class="text-amber-600">5+ hours every month.</span>
            </p>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════
         DIFFERENTIATION
    ═══════════════════════════════════════════════════════ --}}
    <section class="mt-16">
        <div class="marketing-panel mx-auto max-w-3xl px-8 py-10">
            <p class="marketing-eyebrow text-center">Why DailyStars</p>
            <h2 class="marketing-h2 mt-3 text-center">Built differently, on purpose.</h2>

            <ul class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['⭐', 'Kids want to finish their tasks', 'Star rewards and streaks make completion feel like a win — not a chore.'],
                    ['⚡', 'No complicated setup', 'Create kids, add tasks, and start in under 2 minutes. No tutorials needed.'],
                    ['🏠', 'Built for homeschool families', 'Designed around how home learners actually work — not classrooms.'],
                    ['📱', 'Works on tablets and Chromebooks', 'Optimised for the screens kids already use at the kitchen table.'],
                ] as [$icon, $title, $body])
                    <li class="marketing-feature-card flex gap-4">
                        <span class="mt-0.5 text-2xl">{{ $icon }}</span>
                        <div>
                            <p class="font-extrabold text-slate-900">{{ $title }}</p>
                            <p class="marketing-copy mt-1">{{ $body }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════
         FAQ — Alpine.js accordion
    ═══════════════════════════════════════════════════════ --}}
    <section class="mt-16">
        <div class="mx-auto max-w-2xl">
            <p class="marketing-eyebrow text-center">Got Questions?</p>
            <h2 class="marketing-h2 mt-3 text-center">Frequently asked questions</h2>

            <div class="mt-8 divide-y divide-sky-100 overflow-hidden rounded-3xl border border-sky-100 bg-white shadow-sm"
                 x-data="{ open: null }">

                @foreach ([
                    ['Is this hard to set up?',     'Setup takes under 2 minutes. Add your kids, create a few tasks, and you\'re done. No configuration maze.'],
                    ['Do kids need email accounts?', 'Nope. Kids log in with a simple PIN or a shareable link — no email, no password stress.'],
                    ['Can I cancel anytime?',        'Yes. Cancel from your account settings at any time with no fees or hoops to jump through.'],
                    ['Does this work on tablets?',   'Absolutely. DailyStars works great on tablets and Chromebooks — whatever your kids have nearby.'],
                ] as $i => [$question, $answer])
                    <div x-data
                         class="group">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-sky-500"
                            @click="open === {{ $i }} ? open = null : open = {{ $i }}">
                            <span class="font-extrabold text-slate-900">{{ $question }}</span>
                            <span class="flex-shrink-0 text-sky-600 transition-transform duration-200"
                                  :class="open === {{ $i }} ? 'rotate-45' : 'rotate-0'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                        </button>
                        <div x-show="open === {{ $i }}"
                             x-collapse
                             x-cloak>
                            <p class="marketing-copy px-6 pb-5 pt-0">{{ $answer }}</p>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════
         FINAL CTA
    ═══════════════════════════════════════════════════════ --}}
    <section class="mt-16 mb-4">
        <div class="marketing-hero overflow-hidden rounded-3xl px-6 py-14 text-center sm:px-10 sm:py-20">
            <p class="marketing-eyebrow">Ready to start?</p>
            <h2 class="marketing-h2 mx-auto mt-3 max-w-2xl">
                Start using DailyStars today and make your homeschool day smoother.
            </h2>
            <p class="marketing-lead mx-auto mt-4 max-w-xl">
                Free to start. No credit card. Set up in minutes.
            </p>
            <div class="mt-8">
                <a href="{{ route('parent.register') }}"
                   class="marketing-btn marketing-btn-primary inline-block px-10 py-3.5 text-base shadow-lg shadow-sky-400/40 transition hover:scale-[1.03]">
                    Start Free
                </a>
            </div>
        </div>
    </section>

</x-layouts.marketing>
