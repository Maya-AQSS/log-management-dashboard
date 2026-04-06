<div class="text-slate-900 dark:text-slate-100">
    @if($source === 'archived_log')
        <livewire:archived-log-detail :archived-log-id="$recordId" :back-href="$backHref" />
    @else
        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm transition-colors duration-150 dark:border-slate-600 dark:bg-slate-900 dark:shadow-none">
            @if($archivedLogId !== null)
                <div
                    class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-950/25"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                        <p class="flex-1 text-sm text-slate-800 dark:text-slate-200">
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
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.detail.id') }}</div>
                    <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $log->id }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.application') }}</div>
                    <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $log->application?->name ?? '—' }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.severity') }}</div>
                    <div class="mt-1 flex min-h-[2.75rem] items-center rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-inner dark:border-slate-600 dark:bg-slate-950">
                        <x-severity-badge :severity="$log->severity" />
                    </div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.status') }}</div>
                    <div class="mt-1 flex min-h-[2.75rem] items-center rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-inner dark:border-slate-600 dark:bg-slate-950">
                        @if($log->resolved)
                            <span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-200">
                                {{ __('logs.status.resolved') }}
                            </span>
                        @else
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ __('logs.status.unresolved') }}</span>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.error_code') }}</div>
                    <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $log->errorCode?->code ?? '—' }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.created_at') }}</div>
                    <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">
                        {{ optional($log->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.detail.file') }}</div>
                    <div class="mt-1 break-all rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $log->file ?? '—' }}</div>
                </div>

                <div>
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.detail.line') }}</div>
                    <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $log->line ?? '—' }}</div>
                </div>

                <div class="md:col-span-2">
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.message') }}</div>
                    <div
                        class="mt-1 max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 whitespace-pre-wrap break-words shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200 md:max-h-96"
                    >
                        {{ $log->message ?? '—' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.detail.metadata') }}</div>
                    @if($metadataJson !== null)
                        <pre
                            class="mt-1 max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white px-3 py-2.5 font-mono text-xs text-slate-800 whitespace-pre-wrap break-all shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200 md:max-h-96"
                        >{{ $metadataJson }}</pre>
                    @else
                        <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm italic text-slate-500 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-400">{{ __('logs.detail.no_metadata') }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
