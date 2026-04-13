<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between mt-4">
    <div class="flex justify-between flex-1 sm:hidden">
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-text-muted bg-ui-card border border-ui-border cursor-default leading-5 rounded-md dark:text-text-dark-muted dark:bg-ui-dark-card dark:border-ui-dark-border">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-text-primary bg-ui-card border border-ui-border rounded-md leading-5 hover:text-text-secondary focus:outline-none focus:ring ring-odoo-purple/30 active:bg-ui-body active:text-text-primary transition ease-in-out duration-150 dark:bg-ui-dark-card dark:border-ui-dark-border dark:text-text-dark-primary dark:hover:text-text-dark-secondary dark:active:bg-ui-dark-bg">
                {!! __('pagination.previous') !!}
            </button>
        @endif

        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-text-primary bg-ui-card border border-ui-border rounded-md leading-5 hover:text-text-secondary focus:outline-none focus:ring ring-odoo-purple/30 active:bg-ui-body active:text-text-primary transition ease-in-out duration-150 dark:bg-ui-dark-card dark:border-ui-dark-border dark:text-text-dark-primary dark:hover:text-text-dark-secondary dark:active:bg-ui-dark-bg">
                {!! __('pagination.next') !!}
            </button>
        @else
            <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-text-muted bg-ui-card border border-ui-border cursor-default leading-5 rounded-md dark:text-text-dark-muted dark:bg-ui-dark-card dark:border-ui-dark-border">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between mx-4">
        <div>
            <p class="text-sm text-text-secondary leading-5 dark:text-text-dark-secondary">
                @if ($paginator->firstItem())
                    {{ __('pagination.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
                @else
                    {{ $paginator->count() }} {{ __('of') }} {{ $paginator->total() }} {{ __('results') }}
                @endif
            </p>
        </div>

        <div>
            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-text-muted bg-ui-card border border-ui-border cursor-default rounded-l-md leading-5 dark:text-text-dark-muted dark:bg-ui-dark-card dark:border-ui-dark-border" aria-hidden="true">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                @else
                    <button wire:click="previousPage" dusk="previousPage" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-text-primary bg-ui-card border border-ui-border rounded-l-md leading-5 hover:text-text-secondary focus:z-10 focus:outline-none focus:border-odoo-purple focus:ring ring-odoo-purple/30 active:bg-ui-body active:text-text-primary transition ease-in-out duration-150 dark:bg-ui-dark-card dark:border-ui-dark-border dark:text-text-dark-primary dark:hover:text-text-dark-secondary dark:active:bg-ui-dark-bg" aria-label="{{ __('pagination.previous') }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-text-secondary bg-ui-card border border-ui-border cursor-default leading-5 dark:text-text-dark-secondary dark:bg-ui-dark-card dark:border-ui-dark-border">{{ $element }}</span>
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-odoo-purple bg-odoo-purple/10 border border-ui-border cursor-default leading-5 dark:text-white dark:bg-odoo-purple dark:border-ui-dark-border">{{ $page }}</span>
                                </span>
                            @else
                                <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-text-primary bg-ui-card border border-ui-border leading-5 hover:text-text-secondary hover:bg-ui-body focus:z-10 focus:outline-none focus:border-odoo-purple focus:ring ring-odoo-purple/30 active:bg-ui-body active:text-text-primary transition ease-in-out duration-150 dark:bg-ui-dark-card dark:border-ui-dark-border dark:text-text-dark-primary dark:hover:bg-ui-dark-bg dark:hover:text-text-dark-secondary dark:active:bg-ui-dark-bg" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <button wire:click="nextPage" dusk="nextPage" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-text-primary bg-ui-card border border-ui-border rounded-r-md leading-5 hover:text-text-secondary focus:z-10 focus:outline-none focus:border-odoo-purple focus:ring ring-odoo-purple/30 active:bg-ui-body active:text-text-primary transition ease-in-out duration-150 dark:bg-ui-dark-card dark:border-ui-dark-border dark:text-text-dark-primary dark:hover:text-text-dark-secondary dark:active:bg-ui-dark-bg" aria-label="{{ __('pagination.next') }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-text-muted bg-ui-card border border-ui-border cursor-default rounded-r-md leading-5 dark:text-text-dark-muted dark:bg-ui-dark-card dark:border-ui-dark-border" aria-hidden="true">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                @endif
            </span>
        </div>
    </div>
</nav>
