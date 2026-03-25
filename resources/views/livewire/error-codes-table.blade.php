<div>
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('error_codes.filters.search') }}
                </label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchInput"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
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

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-base">
            <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left">{{ __('error_codes.table.code') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('error_codes.table.application') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('error_codes.table.name') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('error_codes.table.file') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('error_codes.table.line') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('error_codes.table.actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($errorCodes as $item)
                <tr
                    class="align-top cursor-pointer hover:bg-slate-50"
                    data-href="{{ route('error-codes.show', $item->id) }}"
                    onclick="window.location.href=this.dataset.href"
                >
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $item->code ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700">{{ $item->application?->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700">{{ $item->name ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700">{{ $item->file ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700">{{ $item->line ?? '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                        <div class="flex gap-2">
                            <a
                                href="{{ route('error-codes.edit', $item->id) }}"
                                onclick="event.stopPropagation()"
                                class="inline-flex items-center rounded-full border border-slate-300 bg-white px-3 py-1 text-sm font-semibold text-slate-800 hover:bg-slate-50"
                            >
                                {{ __('error_codes.buttons.edit') }}
                            </a>

                            <form
                                method="POST"
                                action="{{ route('error-codes.destroy', $item->id) }}"
                                onclick="event.stopPropagation()"
                                onsubmit="return confirm('{{ __('error_codes.messages.delete_confirm') }}')"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-3 py-1 text-sm font-semibold text-red-700 hover:bg-red-100"
                                >
                                    {{ __('error_codes.buttons.delete') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-base text-slate-500">
                        {{ __('error_codes.empty') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $errorCodes->links() }}
        </div>
    </div>
</div>