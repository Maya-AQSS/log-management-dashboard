@php
    $fromLabel = $fromLabel ?? __('logs.filters.date_from');
    $toLabel = $toLabel ?? __('logs.filters.date_to');
@endphp

<div
    x-data="{
        localFrom: '',
        localTo: '',
        toAtomUtc(v) {
            if (!v) return '';
            return v.toISOString().replace(/\.\d{3}Z$/, '+00:00');
        },
        toIsoStart(v) {
            if (!v) return '';
            return this.toAtomUtc(new Date(`${v}T00:00:00`));
        },
        toIsoEnd(v) {
            if (!v) return '';
            return this.toAtomUtc(new Date(`${v}T23:59:59`));
        }
    }"
    x-on:date-range-reset.window="localFrom = ''; localTo = ''"
>
    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                {{ $fromLabel }}
            </label>
            <input
                type="date"
                x-model="localFrom"
                x-on:change="$wire.set('{{ $wireModelFrom }}', toIsoStart(localFrom), false)"
                aria-label="{{ $fromLabel }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
            />
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                {{ $toLabel }}
            </label>
            <input
                type="date"
                x-model="localTo"
                x-on:change="$wire.set('{{ $wireModelTo }}', toIsoEnd(localTo), false)"
                aria-label="{{ $toLabel }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
            />
        </div>
    </div>
</div>

