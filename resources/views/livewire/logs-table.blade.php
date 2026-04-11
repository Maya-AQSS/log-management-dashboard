<div x-data="{}">
    <div class="mt-4 rounded-lg border border-ui-border bg-ui-body p-4 dark:border-ui-dark-border dark:bg-ui-dark-card">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                        {{ __('logs.filters.search') }}
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="searchInput"
                        class="w-full rounded-lg border border-ui-border bg-ui-card px-3 py-2 text-sm shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary focus:border-odoo-purple focus:outline-none focus:ring-2 focus:ring-odoo-purple/20"
                        placeholder="{{ __('logs.filters.search_placeholder') }}"
                    />
                </div>

                <x-filters.severity-filter-checkboxes
                    wire-model="severityInput"
                    :selected="$severityInput"
                />
            </div>

            <div class="space-y-4">
                <div>
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

                <x-filters.application-select
                    wire:model.defer="selectedApplicationIdInput"
                    :hide-label="true"
                    :placeholder="__('logs.filters.application_all')"
                    :applications="$applications"
                    :selected="$selectedApplicationIdInput"
                    class="text-sm font-medium"
                />

                @php
                    $hasResolvedSelected = in_array($resolvedInput, ['resolved', 'unresolved'], true);
                @endphp
                <details
                    @if($hasResolvedSelected) open @endif
                    class="group rounded-lg border border-ui-border bg-ui-card px-3 py-2 shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card"
                >
                    <summary class="list-none cursor-pointer select-none flex items-center justify-between gap-3 text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                        <span>{{ __('logs.filters.resolved_group') }}</span>
                        <x-chevron-down class="transition-transform group-open:rotate-180" />
                    </summary>

                    <div class="mt-2 grid grid-cols-1 gap-2">
                        <label class="flex items-center gap-2 text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                            <input
                                type="radio"
                                name="resolved-filter"
                                value=""
                                wire:model.defer="resolvedInput"
                                class="h-4 w-4 border-ui-border bg-ui-card text-odoo-purple shadow-sm focus:ring-odoo-purple/30 dark:border-ui-dark-border dark:bg-ui-dark-card"
                            />
                            {{ __('logs.filters.resolved_all') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                            <input
                                type="radio"
                                name="resolved-filter"
                                value="resolved"
                                wire:model.defer="resolvedInput"
                                class="h-4 w-4 border-ui-border bg-ui-card text-odoo-purple shadow-sm focus:ring-odoo-purple/30 dark:border-ui-dark-border dark:bg-ui-dark-card"
                            />
                            {{ __('logs.filters.resolved_resolved') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                            <input
                                type="radio"
                                name="resolved-filter"
                                value="unresolved"
                                wire:model.defer="resolvedInput"
                                class="h-4 w-4 border-ui-border bg-ui-card text-odoo-purple shadow-sm focus:ring-odoo-purple/30 dark:border-ui-dark-border dark:bg-ui-dark-card"
                            />
                            {{ __('logs.filters.resolved_unresolved') }}
                        </label>
                    </div>
                </details>
            </div>
        </div>

        <div class="mt-4 flex w-full justify-center gap-2">
            <button
                type="button"
                x-on:click="$dispatch('logs-apply-requested')"
                class="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
            >
                {{ __('logs.buttons.apply') }}
            </button>

            <button
                type="button"
                wire:click="resetFilters"
                class="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
            >
                {{ __('logs.buttons.reset') }}
            </button>
        </div>
    </div>

    <x-index-table
        :emptyText="__('logs.empty')"
        :hasItems="$logs->isNotEmpty()"
        :paginator="$logs"
        table-class="min-w-[56rem]"
    >
        <x-slot:head>
            <tr>
                <th class="px-3 py-2 text-left">
                    <button
                        type="button"
                        wire:click="sortByColumn('application')"
                        title="{{ __('logs.table.sortable_hint') }}"
                        class="inline-flex w-full min-w-0 items-center gap-2.5 select-none uppercase tracking-wide text-left font-bold text-inherit cursor-pointer rounded-sm hover:bg-ui-body dark:hover:bg-ui-dark-border focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-odoo-purple/40"
                    >
                        <span class="truncate">{{ __('logs.table.application') }}</span>
                        @if($sortBy === 'application')
                            <span class="shrink-0 pl-1 text-text-secondary dark:text-text-dark-secondary" aria-hidden="true">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="shrink-0 pl-1 text-xs text-text-muted dark:text-text-dark-muted" aria-hidden="true">↕</span>
                        @endif
                    </button>
                </th>
                <th class="px-3 py-2 text-left">
                    <button
                        type="button"
                        wire:click="sortByColumn('severity')"
                        title="{{ __('logs.table.sortable_hint') }}"
                        class="inline-flex w-full min-w-0 items-center gap-2.5 select-none uppercase tracking-wide text-left font-bold text-inherit cursor-pointer rounded-sm hover:bg-ui-body dark:hover:bg-ui-dark-border focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-odoo-purple/40"
                    >
                        <span class="truncate">{{ __('logs.table.severity') }}</span>
                        @if($sortBy === 'severity')
                            <span class="shrink-0 pl-1 text-text-secondary dark:text-text-dark-secondary" aria-hidden="true">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="shrink-0 pl-1 text-xs text-text-muted dark:text-text-dark-muted" aria-hidden="true">↕</span>
                        @endif
                    </button>
                </th>
                <th class="min-w-[16rem] px-3 py-2 text-left md:min-w-[18rem]">{{ __('logs.table.message') }}</th>
                <th class="px-3 py-2 text-left">{{ __('logs.table.error_code') }}</th>
                <th class="px-3 py-2 text-left">
                    <button
                        type="button"
                        wire:click="sortByColumn('created_at')"
                        title="{{ __('logs.table.sortable_hint') }}"
                        class="inline-flex w-full min-w-0 items-center gap-2.5 select-none uppercase tracking-wide text-left font-bold text-inherit cursor-pointer rounded-sm hover:bg-ui-body dark:hover:bg-ui-dark-border focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-odoo-purple/40"
                    >
                        <span class="truncate">{{ __('logs.table.created_at') }}</span>
                        @if($sortBy === 'created_at')
                            <span class="shrink-0 pl-1 text-text-secondary dark:text-text-dark-secondary" aria-hidden="true">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                        @else
                            <span class="shrink-0 pl-1 text-xs text-text-muted dark:text-text-dark-muted" aria-hidden="true">↕</span>
                        @endif
                    </button>
                </th>
                <th class="px-3 py-2 text-left">{{ __('logs.table.status') }}</th>
            </tr>
        </x-slot:head>

        @foreach($logs as $log)
                <tr
                    class="align-top cursor-pointer hover:bg-ui-body dark:hover:bg-ui-dark-card"
                    data-href="{{ route('logs.show', $log->id) }}"
                    onclick="window.location.href=this.dataset.href"
                >
                    <td class="px-3 py-2 text-text-primary dark:text-text-dark-primary">{{ $log->application?->name ?? '-' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <x-severity-badge :severity="$log->severity" />
                    </td>
                    <td class="min-w-[16rem] max-w-md px-3 py-2 text-text-primary dark:text-text-dark-primary md:min-w-[18rem]">
                        <span class="break-words">{{ \Illuminate\Support\Str::limit($log->message ?? '-', 120) }}</span>
                    </td>
                    <td class="px-3 py-2 text-text-primary whitespace-nowrap dark:text-text-dark-primary">{{ $log->errorCode?->code ?? '-' }}</td>
                    <td class="px-3 py-2 text-text-primary whitespace-nowrap dark:text-text-dark-primary">
                        {{ optional($log->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                    </td>
                    <td class="px-3 py-2 text-text-primary whitespace-nowrap dark:text-text-dark-primary">
                        <div class="flex flex-wrap gap-2">
                            @if($log->resolved)
                                <span class="inline-flex items-center rounded-full bg-info-light px-2 py-0.5 text-xs font-semibold text-info-dark dark:bg-info/20 dark:text-info">
                                    {{ __('logs.status.resolved') }}
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
        @endforeach
    </x-index-table>
</div>
