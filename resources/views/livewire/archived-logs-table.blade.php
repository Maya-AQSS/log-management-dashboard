<div x-data="{}">
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900">
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
                    class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
                >
                    {{ __('archived_logs.buttons.apply') }}
                </button>

                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50"
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
                        class="inline-flex w-full min-w-0 items-center gap-2.5 select-none uppercase tracking-wide text-left font-normal text-inherit cursor-pointer rounded-sm hover:bg-slate-100/70 dark:hover:bg-slate-700/50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5b3853]/40"
                    >
                        <span class="truncate">{{ __('archived_logs.table.severity') }}</span>
                        @if($sortBy === 'severity')
                            <span class="shrink-0 pl-1 text-slate-600 dark:text-slate-300" aria-hidden="true">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="shrink-0 pl-1 text-xs text-slate-400 dark:text-slate-500" aria-hidden="true">↕</span>
                        @endif
                    </button>
                </th>
                <th class="px-3 py-2 text-left">{{ __('archived_logs.table.message') }}</th>
                <th class="px-3 py-2 text-left">
                    <button
                        type="button"
                        wire:click="sortByColumn('archived_at')"
                        title="{{ __('logs.table.sortable_hint') }}"
                        class="inline-flex w-full min-w-0 items-center gap-2.5 select-none uppercase tracking-wide text-left font-normal text-inherit cursor-pointer rounded-sm hover:bg-slate-100/70 dark:hover:bg-slate-700/50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#5b3853]/40"
                    >
                        <span class="truncate">{{ __('archived_logs.table.archived_at') }}</span>
                        @if($sortBy === 'archived_at')
                            <span class="shrink-0 pl-1 text-slate-600 dark:text-slate-300" aria-hidden="true">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="shrink-0 pl-1 text-xs text-slate-400 dark:text-slate-500" aria-hidden="true">↕</span>
                        @endif
                    </button>
                </th>
            </tr>
        </x-slot:head>

        @foreach($archivedLogs as $item)
            <tr
                class="align-top cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800"
                data-href="{{ route('archived-logs.show', $item->id) }}"
                onclick="window.location.href=this.dataset.href"
            >
                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                    {{ $item->application?->name ?? '-' }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    <x-severity-badge :severity="$item->severity" />
                </td>
                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                    {{ \Illuminate\Support\Str::limit($item->message ?? '-', 120) }}
                </td>
                <td class="px-3 py-2 text-slate-700 whitespace-nowrap dark:text-slate-200">
                    {{ optional($item->archived_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                </td>
            </tr>
        @endforeach
    </x-index-table>
</div>
