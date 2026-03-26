<div x-data="{}">
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('logs.filters.search') }}
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="searchInput"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="group rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <summary class="list-none cursor-pointer select-none flex items-center justify-between gap-3 text-sm font-medium text-slate-700 dark:text-slate-200">
                        <span>{{ __('logs.filters.resolved_group') }}</span>
                        <x-chevron-down class="transition-transform group-open:rotate-180" />
                    </summary>

                    <div class="mt-2 grid grid-cols-1 gap-2">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <input
                                type="radio"
                                name="resolved-filter"
                                value=""
                                wire:model.defer="resolvedInput"
                                class="h-4 w-4 border-slate-300 bg-white text-[#5b3853] shadow-sm focus:ring-[#5b3853]/30 dark:border-slate-700 dark:bg-slate-800"
                            />
                            {{ __('logs.filters.resolved_all') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <input
                                type="radio"
                                name="resolved-filter"
                                value="resolved"
                                wire:model.defer="resolvedInput"
                                class="h-4 w-4 border-slate-300 bg-white text-[#5b3853] shadow-sm focus:ring-[#5b3853]/30 dark:border-slate-700 dark:bg-slate-800"
                            />
                            {{ __('logs.filters.resolved_resolved') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <input
                                type="radio"
                                name="resolved-filter"
                                value="unresolved"
                                wire:model.defer="resolvedInput"
                                class="h-4 w-4 border-slate-300 bg-white text-[#5b3853] shadow-sm focus:ring-[#5b3853]/30 dark:border-slate-700 dark:bg-slate-800"
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
                class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
            >
                {{ __('logs.buttons.apply') }}
            </button>

            <button
                type="button"
                wire:click="resetFilters"
                class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50"
            >
                {{ __('logs.buttons.reset') }}
            </button>
        </div>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-base">
            <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <tr>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.application') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.severity') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.message') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.error_code') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.created_at') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.status') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-700 dark:bg-slate-900">
            @forelse($logs as $log)
                <tr
                    class="align-top hover:bg-slate-50 dark:hover:bg-slate-800"
                >
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $log->application?->name ?? '-' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <x-severity-badge :severity="$log->severity" />
                    </td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                        <a
                            href="{{ route('logs.show', $log->id) }}"
                            class="hover:underline focus:outline-none focus:ring-2 focus:ring-[#5b3853]/30 rounded"
                        >
                            {{ \Illuminate\Support\Str::limit($log->message ?? '-', 120) }}
                        </a>
                    </td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap dark:text-slate-200">{{ $log->errorCode?->code ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap dark:text-slate-200">
                        {{ optional($log->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                    </td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap dark:text-slate-200">
                        <div class="flex flex-wrap gap-2">
                            @if($log->resolved)
                                <span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-800">
                                    {{ __('logs.status.resolved') }}
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-base text-slate-500 dark:text-slate-400">
                        {{ __('logs.empty') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-4 flex justify-center">
            {{ $logs->links() }}
        </div>
    </div>
</div>
