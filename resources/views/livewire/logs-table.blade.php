<div>
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
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
                    <option value="">{{ __('logs.filters.severity_all') }}</option>
                    <option value="critical">critical</option>
                    <option value="high">high</option>
                    <option value="medium">medium</option>
                    <option value="low">low</option>
                    <option value="other">other</option>
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
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $log->severity ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700">{{ $log->message ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $log->errorCode?->code ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                        {{ optional($log->created_at)->toDateTimeString() ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-3 py-4 text-center text-base text-slate-500">
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
