<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('logs.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-5">{{ __('logs.welcome') }}</p>

    @php
        $items = $logs ?? ($log ? collect([$log]) : collect());
    @endphp

    @if($items->isNotEmpty())
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-base">
                <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">{{ __('logs.table.application') }}</th>
                        <th class="px-3 py-2">{{ __('logs.table.severity') }}</th>
                        <th class="px-3 py-2">{{ __('logs.table.message') }}</th>
                        <th class="px-3 py-2">{{ __('logs.table.error_code') }}</th>
                        <th class="px-3 py-2">{{ __('logs.table.created_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($items as $item)
                        <tr
                            class="align-top cursor-pointer hover:bg-slate-50"
                            data-href="{{ route('logs.show', $item->id) }}"
                            onclick="window.location.href=this.dataset.href"
                        >
                            <td class="px-3 py-2 text-slate-700">{{ $item->application?->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $item->severity ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700">{{ $item->message ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $item->errorCode?->code ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700 whitespace-nowrap">
                                {{ optional($item->created_at)->toDateTimeString() ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(isset($logs))
                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="mt-4 text-base text-slate-500">{{ __('logs.empty') }}</div>
    @endif
</x-layout>
