<x-layout>
    <h1 class="text-xl font-semibold text-center mb-5">{{ __('archived_logs.title') }}</h1>

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
