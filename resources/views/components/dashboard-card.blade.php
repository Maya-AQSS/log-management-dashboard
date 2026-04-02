<a
    href="{{ $href }}"
    class="block rounded-2xl border {{ $borderClass }} {{ $backgroundClass }} min-h-44 p-6 shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5b3853] dark:ring-white/10"
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
