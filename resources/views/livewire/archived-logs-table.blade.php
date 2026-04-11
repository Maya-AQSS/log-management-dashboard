<div x-data="{}">
    <div class="mt-4 rounded-lg border border-ui-border bg-ui-body p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5 md:items-end">
            <div class="md:col-span-2">
                <x-date-range-filter
                    wire-model-from="dateFromInput"
                    wire-model-to="dateToInput"
                />
                @error('dateFromInput')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('dateToInput')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-filters.severity-filter-checkboxes
                    wire-model="severityInput"
                    :selected="$severityInput"
                />
            </div>

            <div>
                <x-filters.application-select
                    wire:model.defer="selectedApplicationIdInput"
                    :hide-label="true"
                    :placeholder="__('logs.filters.application_all')"
                    :applications="$applications"
                    :selected="$selectedApplicationIdInput"
                    class="text-sm font-medium"
                />
            </div>

            <div class="flex w-full gap-2 md:justify-end">
                <button
                    type="button"
                    x-on:click="$dispatch('logs-apply-requested')"
                    class="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
                >
                    {{ __('archived_logs.buttons.apply') }}
                </button>

                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
                >
                    {{ __('archived_logs.buttons.reset') }}
                </button>
            </div>
        </div>
    </div>

    <x-index-table
        :emptyText="__('archived_logs.empty')"
        :hasItems="$archivedLogs->isNotEmpty()"
        :paginator="$archivedLogs"
    >
        <x-slot:head>
            <tr>
                <th class="px-3 py-2 text-left">{{ __('archived_logs.table.application') }}</th>
                <th class="px-3 py-2 text-left">
                    <button
                        type="button"
                        wire:click="sortByColumn('severity')"
                        title="{{ __('logs.table.sortable_hint') }}"
                        class="inline-flex w-full min-w-0 items-center gap-2.5 select-none uppercase tracking-wide text-left font-bold text-inherit cursor-pointer rounded-sm hover:bg-ui-body dark:hover:bg-ui-dark-border focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-odoo-purple/40"
                    >
                        <span class="truncate">{{ __('archived_logs.table.severity') }}</span>
                        @if($sortBy === 'severity')
                            <span class="shrink-0 pl-1 text-text-secondary dark:text-text-dark-secondary" aria-hidden="true">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="shrink-0 pl-1 text-xs text-text-muted dark:text-text-dark-muted" aria-hidden="true">↕</span>
                        @endif
                    </button>
                </th>
                <th class="px-3 py-2 text-left">{{ __('archived_logs.table.message') }}</th>
                <th class="px-3 py-2 text-left">
                    <button
                        type="button"
                        wire:click="sortByColumn('archived_at')"
                        title="{{ __('logs.table.sortable_hint') }}"
                        class="inline-flex w-full min-w-0 items-center gap-2.5 select-none uppercase tracking-wide text-left font-bold text-inherit cursor-pointer rounded-sm hover:bg-ui-body dark:hover:bg-ui-dark-border focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-odoo-purple/40"
                    >
                        <span class="truncate">{{ __('archived_logs.table.archived_at') }}</span>
                        @if($sortBy === 'archived_at')
                            <span class="shrink-0 pl-1 text-text-secondary dark:text-text-dark-secondary" aria-hidden="true">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="shrink-0 pl-1 text-xs text-text-muted dark:text-text-dark-muted" aria-hidden="true">↕</span>
                        @endif
                    </button>
                </th>
            </tr>
        </x-slot:head>

        @foreach($archivedLogs as $item)
            <tr
                class="align-top cursor-pointer hover:bg-ui-body dark:hover:bg-ui-dark-card"
                data-href="{{ route('archived-logs.show', $item->id) }}"
                onclick="window.location.href=this.dataset.href"
            >
                <td class="px-3 py-2 text-text-primary dark:text-text-dark-primary">
                    {{ $item->application?->name ?? '-' }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    <x-severity-badge :severity="$item->severity" />
                </td>
                <td class="px-3 py-2 text-text-primary dark:text-text-dark-primary">
                    {{ \Illuminate\Support\Str::limit($item->message ?? '-', 120) }}
                </td>
                <td class="px-3 py-2 text-text-primary whitespace-nowrap dark:text-text-dark-primary">
                    {{ optional($item->archived_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                </td>
            </tr>
        @endforeach
    </x-index-table>
</div>
