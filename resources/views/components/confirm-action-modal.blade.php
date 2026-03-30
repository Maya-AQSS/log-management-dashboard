<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $openVar }}"
        x-on:keydown.escape.window="{{ $openVar }} = false"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-black/50" x-on:click="{{ $openVar }} = false"></div>

        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $resolvedTitle }}</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $resolvedMessage }}</p>

            <div class="mt-4 flex justify-end gap-2">
                <button
                    type="button"
                    x-on:click="{{ $openVar }} = false"
                    class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
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
                            'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' => $confirmVariant === 'danger',
                            'bg-[#5b3853] text-white hover:bg-[#4a2d44]' => $confirmVariant === 'primary',
                        ])
                    >
                        {{ $resolvedConfirmLabel }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
