@php
    $fromLabel = $fromLabel ?? __('logs.filters.date_from');
    $toLabel = $toLabel ?? __('logs.filters.date_to');
    $rangeInvalidMessage = $rangeInvalidMessage ?? __('logs.filters.date_range_invalid');
@endphp

<div
    x-data="{
        localFrom: '',
        localTo: '',
        localError: '',
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
        },
        validateRange() {
            this.localError = '';
            if (!this.localFrom || !this.localTo) return true;

            if (this.localTo < this.localFrom) {
                this.localError = @js($rangeInvalidMessage);
                return false;
            }

            return true;
        }
    }"
    x-on:date-range-reset.window="localFrom = ''; localTo = ''; localError = ''"
    x-on:logs-apply-requested.window="if (validateRange()) { $wire.call('applyFilters') }"
>
    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                {{ $fromLabel }}
            </label>
            <input
                type="date"
                x-model="localFrom"
                x-on:change="$wire.set('{{ $wireModelFrom }}', toIsoStart(localFrom), false); validateRange()"
                aria-label="{{ $fromLabel }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
            />
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-text-dark-primary">
                {{ $toLabel }}
            </label>
            <input
                type="date"
                x-model="localTo"
                x-on:change="$wire.set('{{ $wireModelTo }}', toIsoEnd(localTo), false); validateRange()"
                aria-label="{{ $toLabel }}"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
            />
        </div>
    </div>

    <p
        x-cloak
        x-show="localError"
        x-text="localError"
        class="mt-1 text-xs text-red-600"
    ></p>
</div>

