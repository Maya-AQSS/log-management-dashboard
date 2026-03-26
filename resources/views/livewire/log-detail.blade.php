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
                <div
                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-600 dark:bg-slate-800/70"
                >
                <div class="mb-3 flex items-center justify-between gap-2 border-b border-slate-200 pb-3 dark:border-slate-600">
                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ __('logs.table.url_tutorial') }}</div>
                    @can('update', $archivedLog)
                        @if(!$editingUrlTutorial)
                            <button
                                type="button"
                                wire:click="startEditingUrlTutorial"
                                class="inline-flex shrink-0 items-center justify-center rounded-lg p-1.5 text-slate-600 hover:bg-slate-200/80 hover:text-[#5b3853] dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                                title="{{ __('archived_logs.url_tutorial.edit_aria') }}"
                            >
                                <span class="sr-only">{{ __('archived_logs.url_tutorial.edit_aria') }}</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                        @endif
                    @endcan
                </div>

                @can('update', $archivedLog)
                    @if($editingUrlTutorial)
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-3">
                            <input
                                type="text"
                                inputmode="url"
                                wire:model="urlTutorialInput"
                                @class([
                                    'w-full min-w-0 flex-1 rounded-xl border bg-white px-3 py-2 text-sm text-slate-800 shadow-sm dark:bg-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2',
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500' => $errors->has('urlTutorialInput'),
                                    'border-slate-300 focus:border-[#5b3853] focus:ring-[#5b3853]/20 dark:border-slate-600' => !$errors->has('urlTutorialInput'),
                                ])
                                placeholder="{{ __('archived_logs.url_tutorial.placeholder') }}"
                                autocomplete="off"
                            />
                            <div class="flex shrink-0 flex-wrap gap-2">
                                <button
                                    type="button"
                                    wire:click="updateUrlTutorial"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44] disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="updateUrlTutorial">{{ __('archived_logs.url_tutorial.save') }}</span>
                                    <span wire:loading wire:target="updateUrlTutorial">{{ __('archived_logs.url_tutorial.save') }}…</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="cancelEditingUrlTutorial"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    {{ __('archived_logs.url_tutorial.cancel') }}
                                </button>
                            </div>
                        </div>
                        @error('urlTutorialInput')
                            <p
                                class="mt-2 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50"
                                role="alert"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    @else
                        @if(!blank($archivedLog->url_tutorial))
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
                                <p class="min-w-0 flex-1 break-all text-sm text-slate-700 dark:text-slate-300" title="{{ $archivedLog->url_tutorial }}">
                                    {{ \Illuminate\Support\Str::limit($archivedLog->url_tutorial, 120) }}
                                </p>
                                <a
                                    href="{{ $archivedLog->url_tutorial }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex w-fit shrink-0 items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44] sm:ml-2"
                                >
                                    {{ __('archived_logs.buttons.view_tutorial') }}
                                </a>
                            </div>
                        @else
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('archived_logs.url_tutorial.empty') }}</p>
                        @endif
                    @endif
                @endcan

                @cannot('update', $archivedLog)
                    @if(!blank($archivedLog->url_tutorial))
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
                            <p class="min-w-0 flex-1 break-all text-sm text-slate-700 dark:text-slate-300" title="{{ $archivedLog->url_tutorial }}">
                                {{ \Illuminate\Support\Str::limit($archivedLog->url_tutorial, 120) }}
                            </p>
                            <a
                                href="{{ $archivedLog->url_tutorial }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex w-fit shrink-0 items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44] sm:ml-2"
                            >
                                {{ __('archived_logs.buttons.view_tutorial') }}
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">—</p>
                    @endif
                @endcannot
                </div>
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
        </div>
    @endif
</div>
