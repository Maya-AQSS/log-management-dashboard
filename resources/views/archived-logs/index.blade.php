<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('archived_logs.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-5">{{ __('archived_logs.welcome') }}</p>

    @php
        $items = $archivedLogs ?? ($archivedLog ? collect([$archivedLog]) : collect());
    @endphp

    @if($items->isNotEmpty())
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-base">
                <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">{{ __('archived_logs.table.application') }}</th>
                        <th class="px-3 py-2">{{ __('archived_logs.table.severity') }}</th>
                        <th class="px-3 py-2">{{ __('archived_logs.table.message') }}</th>
                        <th class="px-3 py-2">{{ __('archived_logs.table.archived_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($items as $item)
                        <tr
                            class="align-top cursor-pointer hover:bg-slate-50"
                            data-href="{{ route('archived-logs.show', $item->id) }}"
                            onclick="window.location.href=this.dataset.href"
                        >
                            <td class="px-3 py-2 text-slate-700">
                                {{ $item->application?->name ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                                {{ $item->severity ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-slate-700">
                                {{ $item->message ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                                {{ optional($item->archived_at)->toDateTimeString() ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(isset($archivedLogs))
            <div class="mt-4">
                {{ $archivedLogs->links() }}
            </div>
        @endif
    @else
        <div class="mt-4 text-base text-slate-500">{{ __('archived_logs.empty') }}</div>
    @endif
</x-layout>
