<a
    href="{{ $href }}"
    class="block rounded-2xl border {{ $borderClass }} {{ $backgroundClass }} p-6 min-h-44 shadow-sm hover:shadow-md transition ring-1 ring-black/5 dark:ring-white/10"
>
    <div class="text-2xl font-extrabold uppercase tracking-wide {{ $accentTextClass }}">
        {{ $title }}
    </div>

    <div class="mt-6 flex items-end justify-between gap-6">
        <div>
            <div class="text-xs uppercase tracking-wide {{ $accentTextClass }}">{{ $unresolvedLabel }}</div>
            <div class="text-2xl font-bold leading-none {{ $accentTextClass }}">{{ $unresolvedCount }}</div>
        </div>
        <div class="text-right">
            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-200/80">{{ $resolvedLabel }}</div>
            <div class="text-2xl font-bold leading-none text-slate-600 dark:text-slate-100">{{ $resolvedCount }}</div>
        </div>
    </div>
</a>
