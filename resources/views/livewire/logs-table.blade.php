<div>
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('logs.filters.search') }}
                </label>
                <input
                    type="text"
                    wire:model.defer="searchInput"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                    placeholder="{{ __('logs.filters.search_placeholder') }}"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('logs.filters.severity') }}
                </label>
                <select
                    wire:model.defer="severityInput"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                >
                    <option value="">{{ __('severity.all') }}</option>
                    <option value="critical">{{ __('severity.critical') }}</option>
                    <option value="high">{{ __('severity.high') }}</option>
                    <option value="medium">{{ __('severity.medium') }}</option>
                    <option value="low">{{ __('severity.low') }}</option>
                    <option value="other">{{ __('severity.other') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('logs.filters.archived') }}
                </label>
                <select
                    wire:model.defer="archivedInput"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                >
                    <option value="">{{ __('logs.filters.archived_all') }}</option>
                    <option value="archived">{{ __('logs.filters.archived_archived') }}</option>
                    <option value="not_archived">{{ __('logs.filters.archived_not_archived') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('logs.filters.resolved') }}
                </label>
                <select
                    wire:model.defer="resolvedInput"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                >
                    <option value="">{{ __('logs.filters.resolved_all') }}</option>
                    <option value="resolved">{{ __('logs.filters.resolved_resolved') }}</option>
                    <option value="unresolved">{{ __('logs.filters.resolved_unresolved') }}</option>
                </select>
            </div>

            <div class="flex gap-2 md:justify-end">
                <button
                    type="button"
                    wire:click="applyFilters"
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
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-base">
            <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.application') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.severity') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.message') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.error_code') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.created_at') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.status') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($logs as $log)
                <tr
                    class="align-top cursor-pointer hover:bg-slate-50"
                    data-href="{{ route('logs.show', $log->id) }}"
                    onclick="window.location.href=this.dataset.href"
                >
                    <td class="px-3 py-2 text-slate-700">{{ $log->application?->name ?? '-' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <x-severity-badge :severity="$log->severity" />
                    </td>
                    <td class="px-3 py-2 text-slate-700">{{ $log->message ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $log->errorCode?->code ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                        {{ optional($log->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                    </td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                        <div class="flex flex-wrap gap-2">
                            @if($log->resolved)
                                <span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-800">
                                    {{ __('logs.status.resolved') }}
                                </span>
                            @endif

                            @if((bool) ($log->is_archived ?? false))
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                    {{ __('logs.status.archived') }}
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-base text-slate-500">
                        {{ __('logs.empty') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
