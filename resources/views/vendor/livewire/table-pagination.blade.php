<div class="mt-4 flex flex-col items-center gap-2">
    <p class="text-sm text-slate-700 dark:text-slate-300">
        @php
            $from = $paginator->firstItem() ?: 0;
            $to = $paginator->lastItem() ?: 0;
            $total = $paginator->total();
        @endphp
        {{ __('pagination.showing', ['from' => $from, 'to' => $to, 'total' => $total]) }}
    </p>

    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center">
        <div class="inline-flex items-center rounded-md shadow-sm">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 cursor-not-allowed leading-5 rounded-l-md dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-odoo-purple border border-odoo-purple leading-5 hover:bg-odoo-purple-d hover:border-odoo-purple-d focus:outline-none focus:ring-2 focus:ring-odoo-purple/30 active:bg-[#3f243c] dark:bg-odoo-purple dark:border-odoo-purple dark:hover:bg-odoo-purple-d rounded-l-md transition-colors duration-150"
                    aria-label="{{ __('pagination.previous') }}"
                >
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            {{-- Page numbers (compact: first 2, current +/- 1, last 2) --}}
            @php
                $lastPage = (int) $paginator->lastPage();
                $currentPage = (int) $paginator->currentPage();

                $pagesToShow = array_unique(array_merge(
                    array_filter([1, 2, $lastPage - 1, $lastPage], fn ($p) => $p >= 1 && $p <= $lastPage),
                    range(max(1, $currentPage - 1), min($lastPage, $currentPage + 1))
                ));
                sort($pagesToShow);

                $prev = null;
            @endphp

            <div class="flex items-center justify-center gap-1 px-3 bg-white border-x border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                @foreach ($pagesToShow as $page)
                    @if ($prev !== null && $page - $prev > 1)
                        <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 dark:text-slate-300 select-none">
                            ...
                        </span>
                    @endif

                    @if ($page === $paginator->currentPage())
                        <span class="inline-flex items-center justify-center min-w-[2.25rem] px-3 py-2 text-sm font-semibold text-slate-900 bg-slate-200 border border-slate-200 rounded-md dark:bg-slate-700 dark:border-slate-700 dark:text-white">
                            {{ $page }}
                        </span>
                    @else
                        <button
                            type="button"
                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                            class="inline-flex items-center justify-center min-w-[2.25rem] px-3 py-2 text-sm font-medium text-slate-700 bg-transparent border border-transparent rounded-md hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-odoo-purple/30 dark:hover:bg-slate-700 dark:text-slate-200"
                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                        >
                            {{ $page }}
                        </button>
                    @endif

                    @php $prev = $page; @endphp
                @endforeach
            </div>

            {{-- Next --}}
            @if (! $paginator->hasMorePages())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 cursor-not-allowed leading-5 rounded-r-md dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400">
                    {!! __('pagination.next') !!}
                </span>
            @else
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-odoo-purple border border-odoo-purple leading-5 hover:bg-odoo-purple-d hover:border-odoo-purple-d focus:outline-none focus:ring-2 focus:ring-odoo-purple/30 active:bg-[#3f243c] dark:bg-odoo-purple dark:border-odoo-purple dark:hover:bg-odoo-purple-d rounded-r-md transition-colors duration-150"
                    aria-label="{{ __('pagination.next') }}"
                >
                    {!! __('pagination.next') !!}
                </button>
            @endif
        </div>
    </nav>
</div>

