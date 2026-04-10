<a
    href="{{ $href }}"
    class="block rounded-lg border {{ $borderClass }} {{ $backgroundClass }} p-4 shadow-card transition hover:-translate-y-0.5 hover:shadow-card-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-odoo-purple"
>
    <p class="text-xs uppercase tracking-wide font-medium {{ $accentTextClass }} opacity-80">
        {{ $title }}
    </p>

    <p class="text-3xl font-bold mt-1 leading-none {{ $accentTextClass }}">
        {{ $unresolvedCount }}
    </p>

    <div class="mt-3 flex items-center justify-between text-xs {{ $accentTextClass }}">
        <span class="opacity-70">{{ $unresolvedLabel }}</span>
        <span class="opacity-60">{{ $resolvedLabel }}: <strong>{{ $resolvedCount }}</strong></span>
    </div>
</a>
