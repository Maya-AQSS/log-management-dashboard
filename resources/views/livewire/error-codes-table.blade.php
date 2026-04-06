<div>
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div>

                <label class="block text-sm font-medium text-slate-700 dark:text-text-dark-primary">
                    {{ __('error_codes.filters.search') }}
                </label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchInput"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary focus:border-odoo-purple focus:outline-none focus:ring-2 focus:ring-odoo-purple/20"
                    placeholder="{{ __('error_codes.filters.search_placeholder') }}"
                />
            </div>

            <x-filters.application-select
                wire:model.defer="filterAppInput"
                :label="__('error_codes.filters.app')"
                :placeholder="__('error_codes.filters.app_all')"
                :applications="$applications"
                :selected="$filterAppInput"
            />


            <div class="flex gap-2 md:justify-end">
                <button
                    type="button"
                    wire:click="applyFilters"
                    class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-base font-semibold text-white hover:bg-odoo-purple-d"
                >
                    {{ __('error_codes.buttons.apply') }}
                </button>

                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50"
                >
                    {{ __('error_codes.buttons.reset') }}
                </button>
            </div>
        </div>
    </div>

    <x-index-table
        :headers="[
            __('error_codes.table.code'),
            __('error_codes.table.application'),
            __('error_codes.table.name'),
            __('error_codes.table.file'),
            __('error_codes.table.line'),
        ]"
        :emptyText="__('error_codes.empty')"
        :hasItems="$errorCodes->isNotEmpty()"
        :paginator="$errorCodes"
    >
        @foreach($errorCodes as $item)
                <tr
                    class="align-top cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800"
                    data-href="{{ route('error-codes.show', $item->id) }}"
                    onclick="window.location.href=this.dataset.href"
                >
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $item->code ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item->application?->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item->file ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item->line ?? '-' }}</td>
                </tr>
        @endforeach
    </x-index-table>
</div>
