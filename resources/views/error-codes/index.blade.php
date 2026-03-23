<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('error_codes.title') }}</h1>

    <form method="GET" action="{{ route('error-codes.index') }}" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('error_codes.filters.severity') }}
                </label>
                <select
                    name="severity"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                >
                    <option value="">{{ __('error_codes.filters.severity_all') }}</option>
                    <option value="critical" @selected(($severity ?? null) === 'critical')>{{ __('error_codes.filters.severity_critical') }}</option>
                    <option value="high" @selected(($severity ?? null) === 'high')>{{ __('error_codes.filters.severity_high') }}</option>
                    <option value="medium" @selected(($severity ?? null) === 'medium')>{{ __('error_codes.filters.severity_medium') }}</option>
                    <option value="low" @selected(($severity ?? null) === 'low')>{{ __('error_codes.filters.severity_low') }}</option>
                    <option value="other" @selected(($severity ?? null) === 'other')>{{ __('error_codes.filters.severity_other') }}</option>
                </select>
            </div>

            <div class="flex gap-2 md:justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
                >
                    {{ __('error_codes.buttons.apply') }}
                </button>

                <a
                    href="{{ route('error-codes.index') }}"
                    class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50"
                >
                    {{ __('error_codes.buttons.reset') }}
                </a>
            </div>
        </div>
    </form>

    <x-index-table
        :headers="[
            __('error_codes.table.name'),
            __('error_codes.table.application'),
            __('error_codes.table.severity'),
            __('error_codes.table.description'),
        ]"
        :emptyText="__('error_codes.empty')"
        :hasItems="$errorCodes->isNotEmpty()"
        :paginator="$errorCodes"
    >
        @foreach($errorCodes as $item)
            <tr
                class="align-top cursor-pointer hover:bg-slate-50"
                data-href="{{ route('error-codes.show', $item->id) }}"
                onclick="window.location.href=this.dataset.href"
            >
                <td class="px-3 py-2 text-slate-700">{{ $item->name ?? '-' }}</td>
                <td class="px-3 py-2 text-slate-700">{{ $item->application?->name ?? '-' }}</td>
                <td class="px-3 py-2 whitespace-nowrap">
                    <x-severity-badge :severity="$item->severity" />
                </td>
                <td class="px-3 py-2 text-slate-700 break-words">{{ $item->description ?? '-' }}</td>
            </tr>
        @endforeach
    </x-index-table>
</x-layout>
