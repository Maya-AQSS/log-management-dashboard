<x-layout>
    @if($source === 'archived_log')
        <livewire:log-detail
            source="archived_log"
            :record-id="$archivedLog->id"
            :back-href="$backHref"
        />

        <div class="mt-6">
            <livewire:comment-thread
                commentableType="archived-log"
                :commentableId="$archivedLog->id"
            />
        </div>
    @else
        <div class="flex items-start justify-between gap-3">
            <a
                href="{{ $backHref }}"
                class="inline-flex items-center rounded-full bg-[#f7a736] px-4 py-2 text-sm font-semibold text-[#1e1a24] shadow-sm hover:bg-[#e28f1f] dark:bg-amber-500 dark:hover:bg-amber-400"
            >
                {{ __('logs.buttons.back') }}
            </a>

            <div class="text-center">
                <h1 class="text-3xl font-bold text-slate-900 md:text-4xl dark:text-slate-100">
                    {{ __('logs.detail.title') }} #{{ $log->id }}
                </h1>
            </div>

            <div class="flex items-center">
                <div x-data="{ confirmArchiveOpen: false }" class="flex items-center gap-2">
                    @if($archivedLogId === null)
                        <button
                            type="button"
                            x-on:click="confirmArchiveOpen = true"
                            class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                        >
                            {{ __('logs.buttons.archive') }}
                        </button>

                        <template x-teleport="body">
                            <div
                                x-cloak
                                x-show="confirmArchiveOpen"
                                x-on:keydown.escape.window="confirmArchiveOpen = false"
                                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                                style="display: none;"
                            >
                                <div class="absolute inset-0 bg-black/50" x-on:click="confirmArchiveOpen = false"></div>

                                <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('logs.buttons.archive') }}</h2>
                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('logs.confirm_archive') }}</p>

                                    <div class="mt-4 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            x-on:click="confirmArchiveOpen = false"
                                            class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                        >
                                            {{ __('logs.buttons.cancel') }}
                                        </button>

                                        <form method="POST" action="{{ route('logs.archive', $log->id) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                                            >
                                                {{ __('logs.buttons.archive') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    @endif

                    @if(!$log->resolved)
                        <form method="POST" action="{{ route('logs.resolve', $log->id) }}">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                            >
                                {{ __('logs.buttons.solved') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <livewire:log-detail
            source="log"
            :record-id="$log->id"
        />
    @endif
</x-layout>
