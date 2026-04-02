<div class="text-slate-900 dark:text-slate-100">
    <div
        class="flex min-h-[2.5rem] items-start justify-between gap-3"
        x-data="{ confirmDeleteArchivedOpen: false }"
    >
        <a
            href="{{ $backHref ?? '#' }}"
            class="inline-flex items-center rounded-full bg-[#f7a736] px-4 py-2 text-sm font-semibold text-[#1e1a24] shadow-sm hover:bg-[#e28f1f] dark:bg-amber-500 dark:hover:bg-amber-400"
        >
            {{ __('logs.buttons.back') }}
        </a>

        <div class="flex flex-1 flex-col items-center gap-1 text-center md:gap-2">
            <h1 class="text-xl font-semibold leading-tight text-slate-900 md:text-2xl dark:text-slate-100">
                {{ __('logs.detail.archived_title') }} #{{ $archivedLog->id }}
            </h1>
            <p class="text-sm leading-snug text-slate-600 dark:text-slate-400">
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ __('archived_logs.table.archived_at') }}</span>
                {{ optional($archivedLog->archived_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '—' }}
                @if($archivedLog->archivedBy)
                    <span class="font-semibold">{{ __('logs.detail.by') }}</span>
                    {{ $archivedLog->archivedBy->name }} #{{ $archivedLog->archivedBy->id }}
                @endif
            </p>
        </div>

        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
            @can('update', $archivedLog)
                @if(!$editingUrlTutorial)
                    <button
                        type="button"
                        wire:click="startEditingArchivedFields"
                        class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
                    >
                        {{ __('archived_logs.buttons.edit') }}
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="saveArchivedDetailChanges"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44] disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="saveArchivedDetailChanges">{{ __('archived_logs.buttons.save') }}</span>
                        <span wire:loading wire:target="saveArchivedDetailChanges">{{ __('archived_logs.buttons.save') }}…</span>
                    </button>
                    <button
                        type="button"
                        wire:click="cancelEditingArchivedFields"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        {{ __('archived_logs.buttons.cancel') }}
                    </button>
                @endif
            @endcan

            @can('delete', $archivedLog)
                @if(!$editingUrlTutorial)
                    <button
                        type="button"
                        x-on:click="confirmDeleteArchivedOpen = true"
                        class="rounded-full bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                    >
                        {{ __('archived_logs.buttons.delete') }}
                    </button>

                    <x-confirm-action-modal
                        intent="delete_archived"
                        :action="route('archived-logs.destroy', $archivedLog->id)"
                        openVar="confirmDeleteArchivedOpen"
                    />
                @endif
            @endcan
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm transition-colors duration-150 dark:border-slate-600 dark:bg-slate-900 dark:shadow-none">
        <div class="grid grid-cols-1 gap-3 text-base md:grid-cols-2">
            <div>
                <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.detail.id') }}</div>
                <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $archivedLog->id }}</div>
            </div>

            <div>
                <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.application') }}</div>
                <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $archivedLog->application?->name ?? '—' }}</div>
            </div>

            <div>
                <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.severity') }}</div>
                <div class="mt-1 flex min-h-[2.75rem] items-center rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-inner dark:border-slate-600 dark:bg-slate-950">
                    <x-severity-badge :severity="$archivedLog->severity" />
                </div>
            </div>

            <div>
                <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.error_code') }}</div>
                <div class="mt-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">{{ $archivedLog->errorCode?->code ?? '—' }}</div>
            </div>

            <div class="md:col-span-2">
                <div
                    @class([
                        'rounded-xl p-4 shadow-sm transition-colors duration-150',
                        'border border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100' => !$editingUrlTutorial,
                        'border-2 border-[#5b3853]/45 bg-[#5b3853]/[0.06] text-slate-900 ring-2 ring-[#5b3853]/25 dark:border-[#5b3853]/50 dark:bg-[#5b3853]/20 dark:text-slate-100 dark:ring-[#5b3853]/35' => $editingUrlTutorial,
                    ])
                >
                    <div class="mb-3 border-b border-slate-200 pb-3 dark:border-slate-500/80">
                        <div class="font-semibold text-slate-900 dark:text-slate-50">{{ __('archived_logs.description.section_title') }}</div>
                    </div>

                    @can('update', $archivedLog)
                        @if($editingUrlTutorial)
                            <div class="flex flex-col gap-2">
                                <textarea
                                    wire:key="archived-description-{{ app()->getLocale() }}"
                                    wire:model="descriptionInput"
                                    rows="6"
                                    class="w-full min-w-0 rounded-xl border border-[#5b3853]/40 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-inner placeholder:text-slate-400 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40 @error('descriptionInput') border-red-500 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500 @enderror"
                                    placeholder="{{ $descriptionPlaceholder }}"
                                ></textarea>
                                @error('descriptionInput')
                                    <p class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div
                                class="min-h-[7.5rem] w-full rounded-xl border border-[#5b3853]/40 bg-white px-3 py-2.5 text-sm shadow-inner dark:border-slate-500 dark:bg-slate-950"
                            >
                                <div @class([
                                    'whitespace-pre-wrap',
                                    'text-slate-900 dark:text-slate-100' => $archivedLog->description !== null && $archivedLog->description !== '',
                                    'text-slate-500 italic dark:text-slate-400' => $archivedLog->description === null || $archivedLog->description === '',
                                ])>{{ $archivedLog->description !== null && $archivedLog->description !== '' ? $archivedLog->description : __('archived_logs.description.empty') }}</div>
                            </div>
                        @endif
                    @endcan

                    @cannot('update', $archivedLog)
                        <div
                            class="min-h-[7.5rem] w-full rounded-xl border border-[#5b3853]/40 bg-white px-3 py-2.5 text-sm shadow-inner dark:border-slate-500 dark:bg-slate-950"
                        >
                            <div @class([
                                'whitespace-pre-wrap',
                                'text-slate-900 dark:text-slate-100' => $archivedLog->description !== null && $archivedLog->description !== '',
                                'text-slate-500 dark:text-slate-400' => $archivedLog->description === null || $archivedLog->description === '',
                            ])>{{ $archivedLog->description !== null && $archivedLog->description !== '' ? $archivedLog->description : '—' }}</div>
                        </div>
                    @endcannot
                </div>
            </div>

            <div class="md:col-span-2">
                <div
                    @class([
                        'rounded-xl p-4 shadow-sm transition-colors duration-150',
                        'border border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100' => !$editingUrlTutorial,
                        'border-2 border-[#5b3853]/45 bg-[#5b3853]/[0.06] text-slate-900 ring-2 ring-[#5b3853]/25 dark:border-[#5b3853]/50 dark:bg-[#5b3853]/20 dark:text-slate-100 dark:ring-[#5b3853]/35' => $editingUrlTutorial,
                    ])
                >
                    <div class="mb-3 border-b border-slate-200 pb-3 dark:border-slate-500/80">
                        <div class="font-semibold text-slate-900 dark:text-slate-50">{{ __('archived_logs.url_tutorial.section_title') }}</div>
                    </div>

                    @can('update', $archivedLog)
                        @if($editingUrlTutorial)
                            <div class="flex flex-col gap-2">
                                <input
                                    wire:key="archived-url-tutorial-{{ app()->getLocale() }}"
                                    type="text"
                                    inputmode="url"
                                    wire:model="urlTutorialInput"
                                    @class([
                                        'w-full min-w-0 rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 shadow-inner placeholder:text-slate-400 focus:outline-none focus:ring-2 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500',
                                        'border-red-500 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500' => $errors->has('urlTutorialInput'),
                                        'border-[#5b3853]/40 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => !$errors->has('urlTutorialInput'),
                                    ])
                                    placeholder="{{ $urlTutorialPlaceholder }}"
                                    autocomplete="off"
                                />
                                @error('urlTutorialInput')
                                    <p
                                        class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50"
                                        role="alert"
                                    >
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @else
                            @if(!blank($archivedLog->url_tutorial))
                                <div class="flex flex-col gap-3">
                                    <div
                                        class="min-h-[2.75rem] w-full break-all rounded-xl border border-[#5b3853]/40 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-inner dark:border-slate-500 dark:bg-slate-950 dark:text-slate-100"
                                        title="{{ $archivedLog->url_tutorial }}"
                                    >
                                        {{ \Illuminate\Support\Str::limit($archivedLog->url_tutorial, 120) }}
                                    </div>
                                    <a
                                        href="{{ $archivedLog->url_tutorial }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex w-fit shrink-0 items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                                    >
                                        {{ __('archived_logs.buttons.view_tutorial') }}
                                    </a>
                                </div>
                            @else
                                <div class="min-h-[2.75rem] w-full rounded-xl border border-[#5b3853]/40 bg-white px-3 py-2.5 text-sm shadow-inner dark:border-slate-500 dark:bg-slate-950">
                                    <p class="text-slate-500 italic dark:text-slate-400">{{ __('archived_logs.url_tutorial.empty') }}</p>
                                </div>
                            @endif
                        @endif
                    @endcan

                    @cannot('update', $archivedLog)
                        @if(!blank($archivedLog->url_tutorial))
                            <div class="flex flex-col gap-3">
                                <div
                                    class="min-h-[2.75rem] w-full break-all rounded-xl border border-[#5b3853]/40 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-inner dark:border-slate-500 dark:bg-slate-950 dark:text-slate-100"
                                    title="{{ $archivedLog->url_tutorial }}"
                                >
                                    {{ \Illuminate\Support\Str::limit($archivedLog->url_tutorial, 120) }}
                                </div>
                                <a
                                    href="{{ $archivedLog->url_tutorial }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex w-fit shrink-0 items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                                >
                                    {{ __('archived_logs.buttons.view_tutorial') }}
                                </a>
                            </div>
                        @else
                            <div class="min-h-[2.75rem] w-full rounded-xl border border-[#5b3853]/40 bg-white px-3 py-2.5 text-sm shadow-inner dark:border-slate-500 dark:bg-slate-950">
                                <p class="text-slate-500 italic dark:text-slate-400">—</p>
                            </div>
                        @endif
                    @endcannot
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('logs.table.message') }}</div>
                <div
                    class="mt-1 max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 whitespace-pre-wrap break-words shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200 md:max-h-96"
                >
                    {{ $archivedLog->message ?? '—' }}
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
</div>
