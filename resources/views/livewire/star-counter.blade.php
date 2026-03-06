<div class="mt-2 text-2xl">
    @for($i = 0; $i < $stars; $i++)
        <span>⭐</span>
    @endfor
    @if($stars === 0)
        <span class="text-lg font-semibold text-slate-500">No stars yet</span>
    @endif
</div>
