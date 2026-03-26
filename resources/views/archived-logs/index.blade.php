<x-layout>
    <h1 class="text-xl font-semibold text-center mb-5">{{ __('archived_logs.title') }}</h1>

    <form method="GET" action="{{ route('archived-logs.index') }}" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('archived_logs.filters.severity') }}
                </label>
                <div class="relative mt-1">
                    <select
                        name="severity"
                        class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                    >
                        <option value="">{{ __('severity.all') }}</option>
                        <option value="critical" @selected(($severity ?? null) === 'critical')>{{ __('severity.critical') }}</option>
                        <option value="high" @selected(($severity ?? null) === 'high')>{{ __('severity.high') }}</option>
                        <option value="medium" @selected(($severity ?? null) === 'medium')>{{ __('severity.medium') }}</option>
                        <option value="low" @selected(($severity ?? null) === 'low')>{{ __('severity.low') }}</option>
                        <option value="other" @selected(($severity ?? null) === 'other')>{{ __('severity.other') }}</option>
                    </select>
                    <x-chevron-down class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('archived_logs.filters.tutorial') }}
                </label>
                <div class="relative mt-1">
                    <select
                        name="tutorial"
                        class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                    >
                        <option value="">{{ __('archived_logs.filters.tutorial_all') }}</option>
                        <option value="with_tutorial" @selected(($tutorial ?? null) === 'with_tutorial')>{{ __('archived_logs.filters.tutorial_with') }}</option>
                        <option value="without_tutorial" @selected(($tutorial ?? null) === 'without_tutorial')>{{ __('archived_logs.filters.tutorial_without') }}</option>
                    </select>
                    <x-chevron-down class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2" />
                </div>
            </div>

            <div class="flex gap-2 md:justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
                >
                    {{ __('archived_logs.buttons.apply') }}
                </button>

                <a
                    href="{{ route('archived-logs.index') }}"
                    class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50"
                >
                    {{ __('archived_logs.buttons.reset') }}
                </a>
            </div>
        </div>
    </form>

    <x-index-table
        :headers="[
            __('archived_logs.table.application'),
            __('archived_logs.table.severity'),
            __('archived_logs.table.message'),
            __('archived_logs.table.archived_at'),
        ]"
        :emptyText="__('archived_logs.empty')"
        :hasItems="$archivedLogs->isNotEmpty()"
        :paginator="$archivedLogs"
    >
        @foreach($archivedLogs as $item)
            <tr
                class="align-top cursor-pointer hover:bg-slate-50"
                data-href="{{ route('archived-logs.show', $item->id) }}"
                onclick="window.location.href=this.dataset.href"
            >
                <td class="px-3 py-2 text-slate-700">
                    {{ $item->application?->name ?? '-' }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    <x-severity-badge :severity="$item->severity" />
                </td>
                <td class="px-3 py-2 text-slate-700">
                    {{ $item->message ?? '-' }}
                </td>
                <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                    {{ optional($item->archived_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                </td>
            </tr>
        @endforeach
    </x-index-table>
</x-layout>
