@if($hasItems)
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-base">
            <thead class="bg-slate-50 text-base uppercase tracking-wide text-slate-500">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-3 py-2 text-left">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if($paginator)
        <div class="mt-4">
            {{ $paginator->links() }}
        </div>
    @endif
@else
    <div class="mt-4 text-base text-slate-500">{{ $emptyText }}</div>
@endif
