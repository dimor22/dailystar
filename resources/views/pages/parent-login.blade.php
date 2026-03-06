<x-layouts.app :title="'Parent Login'">
    <div class="mx-auto max-w-lg kid-card">
        <h1 class="kid-title text-center">Parent Login</h1>

        <form action="{{ route('parent.login.submit') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-lg font-bold text-slate-700">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-2xl border-2 border-slate-300 px-4 py-3 text-lg"
                >
            </div>

            <div>
                <label for="password" class="mb-2 block text-lg font-bold text-slate-700">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="w-full rounded-2xl border-2 border-slate-300 px-4 py-3 text-lg"
                >
            </div>

            @error('email')
                <p class="font-semibold text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit" class="kid-btn kid-btn-primary w-full">Sign In</button>
        </form>

        <p class="mt-4 text-center text-slate-600">Seeded parent: parent@dailystars.app / password</p>
    </div>
</x-layouts.app>
