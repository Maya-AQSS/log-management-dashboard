<div class="text-text-primary dark:text-text-dark-primary">
    @if($source === 'archived_log')
        <livewire:archived-log-detail :archived-log-id="$recordId" :back-href="$backHref" />
    @else
        <div class="mt-4 rounded-lg border border-ui-border bg-ui-card p-4 shadow-card transition-colors duration-150 dark:border-ui-dark-border dark:bg-ui-dark-card dark:shadow-none">
            @if($archivedLogId !== null)
                <div
                    class="mb-4 rounded-lg border border-warning/30 bg-warning-light p-4 dark:border-warning/30 dark:bg-warning/10"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                        <p class="flex-1 text-sm text-text-primary dark:text-text-dark-primary">
                            {{ __('logs.detail.archived_match') }}
                        </p>
                        <div class="shrink-0">
                            @if($archivedDetailUrl !== null)
                                <a
                                    href="{{ $archivedDetailUrl }}"
                                    class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-odoo-purple-d"
                                >
                                    {{ __('logs.buttons.view_archived') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-3 text-base md:grid-cols-2">
                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.detail.id') }}</div>
                    <div class="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">{{ $log->id }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.table.application') }}</div>
                    <div class="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">{{ $log->application?->name ?? '—' }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.table.severity') }}</div>
                    <div class="mt-1 flex min-h-[2.75rem] items-center rounded-lg border border-ui-border bg-ui-body px-3 py-2 shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg">
                        <x-severity-badge :severity="$log->severity" />
                    </div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.table.status') }}</div>
                    <div class="mt-1 flex min-h-[2.75rem] items-center rounded-lg border border-ui-border bg-ui-body px-3 py-2 shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg">
                        @if($log->resolved)
                            <span class="inline-flex items-center rounded-full bg-info-light px-2 py-0.5 text-xs font-semibold text-info-dark dark:bg-info/20 dark:text-info">
                                {{ __('logs.status.resolved') }}
                            </span>
                        @else
                            <span class="text-sm text-text-secondary dark:text-text-dark-secondary">{{ __('logs.status.unresolved') }}</span>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.table.error_code') }}</div>
                    <div class="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">{{ $log->errorCode?->code ?? '—' }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.table.created_at') }}</div>
                    <div class="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">
                        {{ optional($log->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.detail.file') }}</div>
                    <div class="mt-1 break-all rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">{{ $log->file ?? '—' }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.detail.line') }}</div>
                    <div class="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">{{ $log->line ?? '—' }}</div>
                </div>

                <div class="md:col-span-2">
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.table.message') }}</div>
                    <div
                        class="mt-1 max-h-64 overflow-y-auto rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary whitespace-pre-wrap break-words shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary md:max-h-96"
                    >
                        {{ $log->message ?? '—' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('logs.detail.metadata') }}</div>
                    @if($metadataJson !== null)
                        <pre
                            class="mt-1 max-h-64 overflow-y-auto rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 font-mono text-xs text-text-primary whitespace-pre-wrap break-all shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary md:max-h-96"
                        >{{ $metadataJson }}</pre>
                    @else
                        <div class="mt-1 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm italic text-text-muted shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-muted">{{ __('logs.detail.no_metadata') }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
