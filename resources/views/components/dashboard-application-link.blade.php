@props([
    'href' => '#',
    'name' => '',
    'total' => 0,
])

@php
    $initial = mb_strtoupper(mb_substr((string) $name, 0, 1));
@endphp

<a
    href="{{ $href }}"
    class="group relative flex min-h-[4.5rem] items-stretch gap-3 overflow-hidden rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white via-white to-slate-50/90 p-1 shadow-sm ring-1 ring-slate-900/[0.04] transition-all duration-200 hover:-translate-y-0.5 hover:border-odoo-purple/30 hover:shadow-lg hover:shadow-odoo-purple/[0.08] hover:ring-odoo-purple/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-odoo-purple dark:border-slate-600 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900/95 dark:ring-white/[0.06] dark:hover:border-[#a67f9a]/50 dark:hover:shadow-black/30"
    aria-label="{{ __('dashboard.open_filtered_logs', ['app' => $name]) }}"
>
    <span
        class="flex w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-gradient-to-b from-odoo-purple to-odoo-purple-d text-lg font-bold text-white shadow-inner shadow-black/25"
        aria-hidden="true"
    >
        {{ $initial }}
    </span>

    <span class="flex min-w-0 flex-1 flex-col justify-center py-2 pr-1">
        <span class="truncate text-base font-semibold text-slate-800 group-hover:text-odoo-purple dark:text-slate-100 dark:group-hover:text-[#e8d4e4]">
            {{ $name }}
        </span>
        <span class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
            {{ __('dashboard.logs_total_label') }}
        </span>
    </span>

    <span class="flex shrink-0 items-center pr-2 sm:pr-3">
        <span
            class="inline-flex min-w-[2.75rem] items-center justify-center rounded-full bg-gradient-to-b from-[#f7a736] to-[#e28f1f] px-3 py-1.5 text-lg font-bold tabular-nums text-[#1e1a24] shadow-sm ring-1 ring-amber-900/10 dark:from-amber-500 dark:to-amber-600 dark:text-[#1e1a24]"
        >
            {{ $total }}
        </span>
    </span>
</a>
