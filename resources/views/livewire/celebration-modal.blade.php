<div x-data="{ open: @js($show) }" x-effect="open = @js($show)">
    <div x-show="open" x-cloak class="fixed inset-0 z-40 grid place-items-center bg-indigo-950/60 p-4">
        <div class="max-w-lg rounded-2xl bg-white p-8 text-center shadow-2xl">
            <h2 class="kid-title">Amazing Work! 🎉</h2>
            <p class="mt-3 text-kid-xl text-slate-700">All tasks are done for today. You earned all your stars!</p>
            <div class="mt-6 text-5xl animate-bounce">🌟🌟🌟</div>
            <button class="kid-btn kid-btn-primary mt-6" @click="open = false">Yay!</button>
        </div>
    </div>
</div>
