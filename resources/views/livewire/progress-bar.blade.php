<div>
    <div class="h-5 w-full rounded-full bg-slate-200">
        <div
            class="h-5 rounded-full bg-green-500 transition-all duration-500"
            style="width: {{ $this->percent }}%"
        ></div>
    </div>
    <p class="mt-1 text-sm font-semibold text-slate-600">{{ $current }} / {{ $total }} complete ({{ $this->percent }}%)</p>
</div>
