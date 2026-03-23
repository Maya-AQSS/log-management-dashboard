<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('dashboard.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-6">{{ __('dashboard.welcome') }}</p>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-base">
            <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left">{{ __('logs.table.severity') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('dashboard.count') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($severityCounts as $severity => $count)
                    <tr>
                        <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $severity }}</td>
                        <td class="px-3 py-2 text-slate-700 whitespace-nowrap">{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
