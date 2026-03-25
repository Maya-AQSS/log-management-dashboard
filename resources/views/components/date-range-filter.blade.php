@php
    $label = $label ?? __('logs.filters.date_range');
    $fromLabel = $fromLabel ?? __('logs.filters.date_from');
    $toLabel = $toLabel ?? __('logs.filters.date_to');
@endphp

<div
    x-data="{
        localFrom: '',
        localTo: '',
        toIsoStart(v) {
            if (!v) return '';
            return new Date(`${v}T00:00:00`).toISOString();
        },
        toIsoEnd(v) {
            if (!v) return '';
            return new Date(`${v}T23:59:59.999`).toISOString();
        }
    }"
>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
        {{ $label }}
    </label>

    <div class="mt-2 grid grid-cols-1 gap-2">
        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">
                {{ $fromLabel }}
            </label>
            <input
                type="date"
                x-model="localFrom"
                x-on:change="$wire.set('{{ $wireModelFrom }}', toIsoStart(localFrom), false)"
                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
            />
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">
                {{ $toLabel }}
            </label>
            <input
                type="date"
                x-model="localTo"
                x-on:change="$wire.set('{{ $wireModelTo }}', toIsoEnd(localTo), false)"
                class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
            />
        </div>
    </div>
</div>

