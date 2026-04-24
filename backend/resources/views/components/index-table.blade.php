@if($hasItems)
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-base {{ $tableClass }}">
            <thead class="bg-ui-body text-xs uppercase tracking-wide text-text-secondary dark:bg-ui-dark-card dark:text-text-dark-secondary">
                @isset($head)
                    {{ $head }}
                @else
                    <tr>
                        @foreach($headers as $header)
                            <th class="px-3 py-2 text-left">{{ $header }}</th>
                        @endforeach
                    </tr>
                @endisset
            </thead>

            <tbody class="divide-y divide-ui-border-l bg-ui-card dark:divide-ui-dark-border-l dark:bg-ui-dark-card">
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
    <div class="mt-4 text-sm text-text-secondary dark:text-text-dark-secondary">{{ $emptyText }}</div>
@endif
