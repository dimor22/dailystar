<x-layouts.marketing title="Support Us">
    <section class="marketing-panel p-6 sm:p-10">
        <p class="marketing-eyebrow">Support the Mission</p>
        <h1 class="marketing-h2 mt-3">Help keep DailyStars affordable for homeschool families.</h1>
        <p class="marketing-copy mt-3">Your donation helps fund development time, hosting costs, support tooling, and the ongoing improvements families request most.</p>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <article class="marketing-feature-card">
                <h2 class="marketing-h3">What donations support</h2>
                <ul class="mt-3 space-y-2 text-slate-700">
                    <li>Feature development for homeschool workflows</li>
                    <li>Server costs, security, and reliability</li>
                    <li>Faster bug fixes and parent support</li>
                    <li>New reporting tools for family routines</li>
                </ul>
            </article>

            <article class="marketing-feature-card">
                <h2 class="marketing-h3">Why it matters</h2>
                <p class="marketing-copy mt-3">Every contribution helps keep the app focused on real family needs instead of ad-driven distractions.</p>
            </article>
        </div>

        <div class="mt-7">
            <a href="{{ config('services.marketing.donation_url') }}" target="_blank" rel="noopener noreferrer" class="marketing-btn marketing-btn-accent">Support Us</a>
        </div>
    </section>
</x-layouts.marketing>
