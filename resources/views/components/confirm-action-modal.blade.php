<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $openVar }}"
        x-on:keydown.escape.window="{{ $openVar }} = false"
        data-confirm-modal="{{ $intent }}"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-black/50" x-on:click="{{ $openVar }} = false"></div>

        <div class="relative w-full max-w-md rounded-lg border border-ui-border bg-ui-card p-5 shadow-dropdown dark:border-ui-dark-border dark:bg-ui-dark-card">
            <h2 class="text-lg font-semibold text-text-primary dark:text-text-dark-primary">{{ $resolvedTitle }}</h2>
            <p class="mt-2 text-sm text-text-secondary dark:text-text-dark-secondary">{{ $resolvedMessage }}</p>

            <div class="mt-4 flex justify-end gap-2">
                <button
                    type="button"
                    x-on:click="{{ $openVar }} = false"
                    class="bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
                >
                    {{ $resolvedCancelLabel }}
                </button>

                <form method="POST" action="{{ $action }}">
                    @csrf
                    @if($usesMethodSpoofing)
                        @method($spoofedHttpMethod)
                    @endif
                    <button
                        type="submit"
                        @class([
                            'px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border',
                            'bg-danger text-text-inverse border-danger hover:bg-danger/90' => $confirmVariant === 'danger',
                            'bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d' => $confirmVariant === 'primary',
                        ])
                    >
                        {{ $resolvedConfirmLabel }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
