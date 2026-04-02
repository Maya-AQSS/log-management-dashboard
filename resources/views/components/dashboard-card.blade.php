<a
    href="{{ $href }}"
    class="block rounded-xl border {{ $borderClass }} {{ $backgroundClass }} p-6 min-h-44 shadow-card hover:shadow-card-md transition ring-1 ring-black/5 dark:ring-white/10"
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
            <div class="text-xs uppercase tracking-wide text-text-secondary dark:text-text-dark-secondary">{{ $resolvedLabel }}</div>
            <div class="text-2xl font-bold leading-none text-text-primary dark:text-text-dark-primary">{{ $resolvedCount }}</div>
        </div>
    </div>
</a>
