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
        <div class="flex min-h-[2.5rem] items-start justify-between gap-3">
            <a
                href="{{ $backHref }}"
                class="inline-flex items-center rounded-lg border border-ui-border bg-ui-card px-4 py-2 text-sm font-semibold text-text-primary shadow-card hover:bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary dark:hover:bg-ui-dark-border"
            >
                {{ __('logs.buttons.back') }}
            </a>

            <div class="flex flex-1 flex-col items-center justify-center text-center">
                <h1 class="text-xl font-semibold leading-tight text-text-primary md:text-2xl dark:text-text-dark-primary">
                    {{ __('logs.detail.title') }} #{{ $log->id }}
                </h1>
            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
                <div
                    x-data="{ confirmArchiveOpen: false, confirmResolveOpen: false }"
                    class="flex flex-wrap items-center justify-end gap-2 sm:gap-3"
                >
                    @if($archivedLogId === null)
                        <button
                            type="button"
                            x-on:click="confirmArchiveOpen = true"
                            class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-odoo-purple-d"
                        >
                            {{ __('logs.buttons.archive') }}
                        </button>

                        <x-confirm-action-modal
                            intent="archive"
                            :action="route('logs.archive', $log->id)"
                            openVar="confirmArchiveOpen"
                        />
                    @endif

                    @if(!$log->resolved)
                        <button
                            type="button"
                            x-on:click="confirmResolveOpen = true"
                            class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-odoo-purple-d"
                        >
                            {{ __('logs.buttons.solved') }}
                        </button>

                        <x-confirm-action-modal
                            intent="resolve"
                            :action="route('logs.resolve', $log->id)"
                            openVar="confirmResolveOpen"
                        />
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
