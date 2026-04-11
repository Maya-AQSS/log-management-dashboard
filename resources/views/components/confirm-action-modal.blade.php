<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $openVar }}"
        x-on:keydown.escape.window="{{ $openVar }} = false"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-black/50" x-on:click="{{ $openVar }} = false"></div>

        <div
            class="relative w-full max-w-md rounded-lg border border-ui-border bg-ui-card p-5 shadow-dropdown dark:border-ui-dark-border dark:bg-ui-dark-card"
            data-confirm-modal="{{ in_array($intent, ['delete', 'delete_archived']) ? 'delete' : $intent }}"
        >
            <h2 class="text-lg font-semibold text-text-primary dark:text-text-dark-primary">{{ $resolvedTitle }}</h2>
            <p class="mt-2 text-sm text-text-secondary dark:text-text-dark-secondary">{{ $resolvedMessage }}</p>

            <div class="mt-4 flex justify-end gap-2">
                <button
                    type="button"
                    x-on:click="{{ $openVar }} = false"
                    class="inline-flex items-center rounded-full border border-ui-border bg-ui-card px-4 py-2 text-sm font-semibold text-text-primary hover:bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary dark:hover:bg-ui-dark-border"
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
                            'inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold shadow-sm',
                            'border border-danger/20 bg-danger-light text-danger-dark hover:bg-danger/20' => $confirmVariant === 'danger',
                            'bg-odoo-purple text-white hover:bg-odoo-purple-d' => $confirmVariant === 'primary',
                        ])
                    >
                        {{ $resolvedConfirmLabel }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
