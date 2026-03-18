<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('error_codes.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-5">{{ __('error_codes.welcome') }}</p>

    @php
        $items = $errorCodes ?? collect();
    @endphp

    @if($items->isNotEmpty())
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-base">
                <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('error_codes.table.application') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('error_codes.table.code') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('error_codes.table.severity') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('error_codes.table.name') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('error_codes.table.description') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($items as $item)
                        <tr
                            class="align-top cursor-pointer hover:bg-slate-50"
                            data-href="{{ route('error-codes.show', $item->id) }}"
                            onclick="window.location.href=this.dataset.href"
                        >
                            <td class="px-3 py-2 text-slate-700">{{ $item->application?->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $item->code ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $item->severity ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700">{{ $item->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-700 break-words">{{ $item->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(isset($errorCodes))
                <div class="mt-4">
                    {{ $errorCodes->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="mt-4 text-base text-slate-500">{{ __('error_codes.empty') }}</div>
    @endif
</x-layout>
