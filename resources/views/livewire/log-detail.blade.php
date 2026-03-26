<div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
    @if($source === 'log')
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
                                class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                            >
                                {{ __('logs.buttons.view_archived') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.detail.id') }}</div>
                <div class="text-slate-700 dark:text-slate-300">{{ $log->id }}</div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.application') }}</div>
                <div class="text-slate-700 dark:text-slate-300">{{ $log->application?->name ?? '—' }}</div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.severity') }}</div>
                <div class="text-slate-700 dark:text-slate-300">
                    <x-severity-badge :severity="$log->severity" />
                </div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.status') }}</div>
                <div class="text-slate-700 dark:text-slate-300">
                    @if($log->resolved)
                        <span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-200">
                            {{ __('logs.status.resolved') }}
                        </span>
                    @else
                        <span class="text-slate-600 dark:text-slate-400">{{ __('logs.status.unresolved') }}</span>
                    @endif
                </div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.error_code') }}</div>
                <div class="text-slate-700 dark:text-slate-300">{{ $log->errorCode?->code ?? '—' }}</div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.created_at') }}</div>
                <div class="text-slate-700 dark:text-slate-300">
                    {{ optional($log->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '—' }}
                </div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.detail.file') }}</div>
                <div class="break-all text-slate-700 dark:text-slate-300">{{ $log->file ?? '—' }}</div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.detail.line') }}</div>
                <div class="text-slate-700 dark:text-slate-300">{{ $log->line ?? '—' }}</div>
            </div>

            <div class="md:col-span-2">
                <div class="mb-1 font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.message') }}</div>
                <div
                    class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-3 text-slate-800 whitespace-pre-wrap break-words dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 md:max-h-96"
                >
                    {{ $log->message ?? '—' }}
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="mb-1 font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.detail.metadata') }}</div>
                @if($metadataJson !== null)
                    <pre
                        class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-3 font-mono text-xs text-slate-800 whitespace-pre-wrap break-all dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 md:max-h-96"
                    >{{ $metadataJson }}</pre>
                @else
                    <div class="text-slate-600 dark:text-slate-400">{{ __('logs.detail.no_metadata') }}</div>
                @endif
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.detail.id') }}</div>
                <div class="text-slate-700 dark:text-slate-300">{{ $archivedLog->id }}</div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.application') }}</div>
                <div class="text-slate-700 dark:text-slate-300">{{ $archivedLog->application?->name ?? '—' }}</div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.severity') }}</div>
                <div class="text-slate-700 dark:text-slate-300">
                    <x-severity-badge :severity="$archivedLog->severity" />
                </div>
            </div>

            <div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.error_code') }}</div>
                <div class="text-slate-700 dark:text-slate-300">{{ $archivedLog->errorCode?->code ?? '—' }}</div>
            </div>

            <div class="md:col-span-2">
                <div class="mb-1 font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.message') }}</div>
                <div
                    class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-3 text-slate-800 whitespace-pre-wrap break-words dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 md:max-h-96"
                >
                    {{ $archivedLog->message ?? '—' }}
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="mb-1 font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.detail.metadata') }}</div>
                @if($metadataJson !== null)
                    <pre
                        class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-3 font-mono text-xs text-slate-800 whitespace-pre-wrap break-all dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 md:max-h-96"
                    >{{ $metadataJson }}</pre>
                @else
                    <div class="text-slate-600 dark:text-slate-400">{{ __('logs.detail.no_metadata') }}</div>
                @endif
            </div>

            <div class="md:col-span-2">
                <div class="mb-1 font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.url_tutorial') }}</div>

                @can('update', $archivedLog)
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-3">
                        <input
                            type="text"
                            inputmode="url"
                            wire:model.blur="urlTutorialInput"
                            @class([
                                'w-full min-w-0 flex-1 rounded-xl border bg-white px-3 py-2 text-sm text-slate-800 shadow-sm dark:bg-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2',
                                'border-red-500 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500' => $errors->has('urlTutorialInput'),
                                'border-slate-300 focus:border-[#5b3853] focus:ring-[#5b3853]/20 dark:border-slate-600' => !$errors->has('urlTutorialInput'),
                            ])
                            placeholder="{{ __('archived_logs.url_tutorial.placeholder') }}"
                            autocomplete="off"
                        />
                        <button
                            type="button"
                            wire:click="updateUrlTutorial"
                            wire:loading.attr="disabled"
                            class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44] disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="updateUrlTutorial">{{ __('archived_logs.url_tutorial.save') }}</span>
                            <span wire:loading wire:target="updateUrlTutorial" class="text-sm">{{ __('archived_logs.url_tutorial.save') }}…</span>
                        </button>
                    </div>
                    @error('urlTutorialInput')
                        <p
                            class="mt-2 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50"
                            role="alert"
                        >
                            {{ $message }}
                        </p>
                    @enderror
                @endcan

                @cannot('update', $archivedLog)
                    @if(!blank($archivedLog->url_tutorial))
                        <a
                            href="{{ $archivedLog->url_tutorial }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                        >
                            {{ __('archived_logs.buttons.view_tutorial') }}
                        </a>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">—</p>
                    @endif
                @endcannot

                @can('update', $archivedLog)
                    @if(!blank($archivedLog->url_tutorial))
                        <div class="mt-3">
                            <a
                                href="{{ $archivedLog->url_tutorial }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                {{ __('archived_logs.buttons.view_tutorial') }}
                            </a>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    @endif
</div>
