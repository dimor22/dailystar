<div class="space-y-6">

    {{-- ── Flash message ─────────────────────────────────────────────────── --}}
    @if (session('billing_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-800">
            {{ session('billing_message') }}
        </div>
    @endif

    {{-- ── Current plan badge ─────────────────────────────────────────────── --}}
    <div class="kid-card flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Current Plan</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">
                {{ $plan->label() }}
                @if ($onTrial)
                    <span class="ml-2 rounded-full bg-sky-100 px-2.5 py-0.5 text-sm font-extrabold text-sky-700">Trial</span>
                @endif
                @if ($onGracePeriod)
                    <span class="ml-2 rounded-full bg-amber-100 px-2.5 py-0.5 text-sm font-extrabold text-amber-700">Cancelling</span>
                @endif
            </p>

            @if ($onTrial && $trialEndsAt)
                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Trial ends {{ $trialEndsAt->format('M j, Y') }}
                </p>
            @endif
            @if ($onGracePeriod && $endsAt)
                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Pro access until {{ $endsAt->format('M j, Y') }}
                </p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($plan->isPro() && ! $onGracePeriod)
                {{-- Cancel --}}
                <form action="{{ route('parent.billing.cancel') }}" method="POST">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Cancel your Pro subscription? You keep access until your billing period ends.')"
                            class="kid-btn kid-btn-warn text-sm">
                        Cancel Subscription
                    </button>
                </form>
                {{-- Stripe portal --}}
                <form action="{{ route('parent.billing.portal') }}" method="POST">
                    @csrf
                    <button type="submit" class="kid-btn kid-btn-primary text-sm">
                        Manage Billing
                    </button>
                </form>
            @elseif ($onGracePeriod)
                {{-- Resume --}}
                <form action="{{ route('parent.billing.resume') }}" method="POST">
                    @csrf
                    <button type="submit" class="kid-btn kid-btn-primary text-sm">
                        Resume Subscription
                    </button>
                </form>
            @else
                {{-- Upgrade --}}
                <a href="{{ route('parent.billing.checkout') }}" class="kid-btn kid-btn-primary text-sm">
                    Upgrade to Pro
                </a>
            @endif
        </div>
    </div>

    {{-- ── Plan comparison table ───────────────────────────────────────────── --}}
    <div class="grid gap-5 sm:grid-cols-2">

        {{-- Free card --}}
        <div class="kid-card @if(! $plan->isPro()) ring-2 ring-sky-400 @endif">
            <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Free</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$0<span class="text-base font-semibold text-slate-400">/mo</span></p>
            <ul class="mt-4 space-y-2 text-sm font-semibold text-slate-700">
                <li class="flex items-center gap-2"><span class="text-sky-500">⭐</span> Up to 2 kids</li>
                <li class="flex items-center gap-2"><span class="text-sky-500">⭐</span> Up to 5 tasks per kid</li>
                <li class="flex items-center gap-2"><span class="text-sky-500">⭐</span> Basic star rewards</li>
                <li class="flex items-center gap-2"><span class="text-sky-500">⭐</span> Daily progress view</li>
            </ul>
        </div>

        {{-- Pro card --}}
        <div class="kid-card border-2 border-sky-500 @if($plan->isPro()) ring-2 ring-sky-400 @endif relative">
            <span class="absolute -top-3 left-4 rounded-full bg-amber-400 px-3 py-0.5 text-xs font-extrabold uppercase text-slate-900">
                ⭐ Most Popular
            </span>
            <p class="mt-2 text-xs font-extrabold uppercase tracking-widest text-sky-600">Pro</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900" style="font-family:'Baloo 2',cursive">$12<span class="text-base font-semibold text-slate-400">/mo</span></p>
            <ul class="mt-4 space-y-2 text-sm font-semibold text-slate-700">
                <li class="flex items-center gap-2"><span class="text-amber-500">🌟</span> Unlimited kids</li>
                <li class="flex items-center gap-2"><span class="text-amber-500">🌟</span> Unlimited tasks</li>
                <li class="flex items-center gap-2"><span class="text-amber-500">🌟</span> Points + rewards system</li>
                <li class="flex items-center gap-2"><span class="text-amber-500">🌟</span> Streak bonuses</li>
                <li class="flex items-center gap-2"><span class="text-amber-500">🌟</span> Parent dashboard insights</li>
                <li class="flex items-center gap-2"><span class="text-amber-500">🌟</span> Celebration animations</li>
                <li class="flex items-center gap-2"><span class="text-amber-500">🌟</span> Priority updates</li>
            </ul>
            @if (! $plan->isPro())
                <div class="mt-5">
                    <a href="{{ route('parent.billing.checkout') }}"
                       class="kid-btn kid-btn-primary block w-full text-center">
                        Start 7-Day Free Trial
                    </a>
                    <p class="mt-2 text-center text-xs font-semibold text-slate-400">Cancel anytime.</p>
                </div>
            @endif
        </div>
    </div>
</div>
