<x-layouts.marketing title="Contact DailyStars">
    <section class="marketing-panel p-6 sm:p-10">
        <p class="marketing-eyebrow">Contact</p>
        <h1 class="marketing-h2 mt-3">Send feedback, ideas, or support questions.</h1>
        <p class="marketing-copy mt-2">I read every message. If you are a homeschool parent, include your kids' ages and your routine challenge so I can build features that help.</p>

        @if(session('contact_success'))
            <div class="mt-5 rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 font-semibold text-emerald-800">
                {{ session('contact_success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('marketing.contact.submit') }}" class="mt-6 grid gap-4">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="marketing-label">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="marketing-input" required>
                    @error('name') <p class="marketing-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="marketing-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="marketing-input" required>
                    @error('email') <p class="marketing-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="subject" class="marketing-label">Subject</label>
                <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="marketing-input" required>
                @error('subject') <p class="marketing-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="marketing-label">Message</label>
                <textarea id="message" name="message" rows="7" class="marketing-input" required>{{ old('message') }}</textarea>
                @error('message') <p class="marketing-error">{{ $message }}</p> @enderror
            </div>

            <div class="pt-1">
                <button type="submit" class="marketing-btn marketing-btn-primary">Send Message</button>
            </div>
        </form>
    </section>
</x-layouts.marketing>
