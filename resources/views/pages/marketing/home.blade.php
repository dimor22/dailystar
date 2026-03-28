<x-layouts.marketing title="DailyStars | Homeschool Routines That Stick">
    <section class="marketing-hero overflow-hidden rounded-3xl p-6 sm:p-10">
        <div class="grid items-center gap-8 lg:grid-cols-2">
            <div>
                <p class="marketing-eyebrow">Made for Homeschool Families</p>
                <h1 class="marketing-h1 mt-3">Turn daily school tasks into momentum kids can feel.</h1>
                <p class="marketing-lead mt-4">DailyStars helps parents guide chores, lessons, and habits with less stress. Kids earn points, stars, and streaks while building real consistency.</p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('parent.register') }}" class="marketing-btn marketing-btn-primary">Start Free as a Parent</a>
                    <a href="{{ route('marketing.contact') }}" class="marketing-btn marketing-btn-outline">Request Early Access Help</a>
                </div>

                <p class="mt-4 text-sm font-semibold text-slate-600">Best for families with kids ages 4-14 doing homeschool, chores, and independent routines.</p>
            </div>

            <div class="marketing-panel p-6 sm:p-8">
                <p class="marketing-panel-title">What parents love most</p>
                <ul class="mt-4 space-y-3 text-slate-700">
                    <li>One dashboard for all kids and all daily tasks</li>
                    <li>PIN-protected kid mode for focused independence</li>
                    <li>Automatic streaks that reward full daily follow-through</li>
                    <li>Simple progress visibility without complex setup</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="marketing-feature-card">
            <h2 class="marketing-h3">Kid-Friendly Task View</h2>
            <p class="marketing-copy mt-2">Each child sees only what matters today, with clear visuals and easy completion flow.</p>
        </article>
        <article class="marketing-feature-card">
            <h2 class="marketing-h3">Parent Command Center</h2>
            <p class="marketing-copy mt-2">Track points, stars, streaks, and completion status across your whole homeschool day.</p>
        </article>
        <article class="marketing-feature-card">
            <h2 class="marketing-h3">Routine Consistency</h2>
            <p class="marketing-copy mt-2">DailyStars encourages complete-day wins, helping routines become habits over time.</p>
        </article>
        <article class="marketing-feature-card">
            <h2 class="marketing-h3">Fast to Start</h2>
            <p class="marketing-copy mt-2">Create kids, assign tasks, and begin in minutes without a complicated onboarding process.</p>
        </article>
    </section>

    <section class="mt-8 marketing-panel p-6 sm:p-8">
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <p class="marketing-stat">5 minutes</p>
                <p class="marketing-copy">Typical setup time for your first family routine.</p>
            </div>
            <div>
                <p class="marketing-stat">Daily</p>
                <p class="marketing-copy">Progress updates that keep kids connected to their goals.</p>
            </div>
            <div>
                <p class="marketing-stat">Family-first</p>
                <p class="marketing-copy">Built specifically for home learning rhythms and parent-led routines.</p>
            </div>
        </div>
    </section>
</x-layouts.marketing>
