<div x-data="{ confirmDeleteOpen: false }">
    <!-- BEGIN: Root wrapper for Livewire single root requirement -->
    <div>
        <div class="flex min-h-[2.5rem] items-start justify-between gap-3">
        <a
            href="{{ $backHref ?? route('archived-logs.index') }}"
            class="bg-transparent text-text-secondary dark:text-text-dark-secondary border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
        >
            {{ __('archived_logs.buttons.back') }}
        </a>

        <div class="flex flex-1 flex-col items-center gap-1 text-center md:gap-2">
            <h1 class="text-xl font-semibold leading-tight text-text-primary md:text-2xl dark:text-text-dark-primary">{{ __('archived_logs.detail.title') }}</h1>
            <p class="hidden md:block text-sm leading-snug text-text-secondary dark:text-text-dark-secondary">
                {{ $isEditable ? __('archived_logs.detail.edit_subtitle') : __('archived_logs.detail.subtitle') }}
            </p>
        </div>

        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
            @if(! $isEditable)
                <button
                    type="button"
                    wire:click="enableEdit"
                    class="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
                >
                    {{ __('archived_logs.buttons.edit') }}
                </button>
                <button
                    type="button"
                    x-on:click="confirmDeleteOpen = true"
                    class="inline-flex items-center rounded-full bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                >
                    {{ __('archived_logs.buttons.delete') }}
                </button>
            @else
                <button
                    type="button"
                    wire:click="cancelEdit"
                    class="inline-flex items-center bg-transparent text-text-secondary dark:text-text-dark-secondary border border-ui-border dark:border-ui-dark-border hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer"
                >
                    {{ __('archived_logs.buttons.cancel') }}
                </button>
                <button
                    type="submit"
                    form="archived-log-main-form"
                    class="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
                >
                    {{ __('archived_logs.buttons.save') }}
                </button>
            @endif
        </div>
        </div>

        @php
            $formActive = $isEditable;
        @endphp

        <form
            id="archived-log-main-form"
            wire:submit.prevent="save"
            class="mt-4"
        >
            <div
                @class([
                    'rounded-xl p-4 shadow-sm transition-colors duration-150',
                    'border border-ui-border bg-ui-card text-text-primary dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary' => ! $formActive,
                    'border-2 border-odoo-purple/45 bg-odoo-purple/[0.06] text-text-primary ring-2 ring-odoo-purple/25 dark:border-odoo-purple/50 dark:bg-odoo-purple/20 dark:text-text-dark-primary dark:ring-odoo-purple/35' => $formActive,
                ])
            >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-base">

                {{-- application --}}
                <div>
                    <label for="app_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.application') }}</label>
                    <div class="mt-1 rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">
                        {{ $archivedLog->application?->name ?? '—' }}
                    </div>
                </div>

                {{-- status (resolved) edit --}}
                <div>
                    <label for="resolved" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.resolved') }}</label>
                    @if($formActive)
                        <label class="relative mt-1 inline-flex cursor-pointer items-center gap-2">
                            <input
                                id="resolved"
                                type="checkbox"
                                wire:model="resolved"
                                class="peer sr-only"
                                role="switch"
                                @disabled(! $isEditable)
                            />
                            <span class="h-6 w-11 rounded-full border border-ui-border bg-ui-card transition-colors peer-checked:border-odoo-purple/40 peer-checked:bg-odoo-purple/20 dark:border-ui-dark-border dark:bg-ui-dark-card dark:peer-checked:border-odoo-purple/60 dark:peer-checked:bg-odoo-purple/30"></span>
                            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5 dark:bg-text-dark-muted"></span>
                            <span class="text-sm text-text-secondary dark:text-text-dark-secondary">
                                {{ $resolved ? __('archived_logs.detail.resolved_yes') : __('archived_logs.detail.resolved_no') }}
                            </span>
                        </label>
                    @else
                        <div class="mt-1 flex min-h-[2.75rem] items-center rounded-xl border border-ui-border bg-ui-body px-3 py-2 shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg">
                            @if($archivedLog->resolved)
                                <span class="inline-flex items-center rounded-full bg-info-light px-2 py-0.5 text-xs font-semibold text-info-dark dark:bg-info/20 dark:text-info">
                                    {{ __('logs.status.resolved') }}
                                </span>
                            @else
                                <span class="text-sm text-text-secondary dark:text-text-dark-secondary">{{ __('logs.status.unresolved') }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- severity --}}
                <div>
                    <label for="severity_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.severity') }}</label>
                    <div class="mt-1 flex min-h-[2.75rem] items-center rounded-xl border border-ui-border bg-ui-body px-3 py-2 shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg">
                        <x-severity-badge :severity="$archivedLog->severity" />
                    </div>
                </div>

                {{-- error code (editable) --}}
                <div>
                    <label for="error_code_id" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.error_code') }}</label>
                    @if($formActive)
                        <div class="relative mt-1">
                            <select
                                id="error_code_id"
                                wire:model="errorCodeId"
                                @class([
                                    'mt-1 w-full appearance-none rounded-xl border px-3 py-2.5 pr-10 text-sm shadow-inner focus:outline-none focus:ring-2',
                                    'border-odoo-purple/40 bg-white text-text-primary focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:text-text-dark-primary dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40' => $formActive,
                                ])
                                @disabled(! $isEditable)
                            >
                                <option value="">— {{ __('archived_logs.detail.no_error_code') }} —</option>
                                @foreach($errorCodes as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-text-muted dark:text-text-dark-muted">
                                <x-chevron-down class="h-4 w-4" />
                            </span>
                        </div>
                        @error('errorCodeId')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @else
                        <div class="mt-1 rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">
                            {{ $archivedLog->errorCode?->code ?? '—' }}
                        </div>
                    @endif
                </div>

                {{-- message --}}
                <div class="md:col-span-2">
                    <label for="message_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.message') }}</label>
                    <div class="mt-1 max-h-64 overflow-y-auto rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary whitespace-pre-wrap break-words shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary md:max-h-96">
                        {{ $archivedLog->message ?? '—' }}
                    </div>
                </div>

                {{-- internal notes (editable) --}}
                <div class="md:col-span-2">
                    <label for="internal_notes" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
                        {{ __('archived_logs.detail.internal_notes') }}
                    </label>
                    @if($formActive)
                        <textarea
                            id="internal_notes"
                            wire:model="internalNotes"
                            rows="4"
                            @class([
                                'mt-1 w-full min-w-0 rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2',
                                'border-odoo-purple/40 bg-white text-text-primary placeholder:text-text-muted focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:text-text-dark-primary dark:placeholder:text-text-dark-muted dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40' => $formActive,
                            ])
                            @disabled(! $isEditable)
                        >{{ $internalNotes }}</textarea>
                        @error('internalNotes')<p class="mt-1 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50" role="alert">{{ $message }}</p>@enderror
                    @else
                        <div class="mt-1 max-h-40 overflow-y-auto rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary whitespace-pre-wrap shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">
                            {{ filled($archivedLog->internal_notes) ? $archivedLog->internal_notes : '—' }}
                        </div>
                    @endif
                </div>

                {{-- file --}}
                <div>
                    <label for="file_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.file') }}</label>
                    <div class="mt-1 break-all rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">{{ $archivedLog->file ?? '—' }}</div>
                </div>

                {{-- line --}}
                <div>
                    <label for="line_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.line') }}</label>
                    <div class="mt-1 rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">{{ $archivedLog->line ?? '—' }}</div>
                </div>

                {{-- archived_at --}}
                <div>
                    <label for="archived_at_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.archived_at') }}</label>
                    <div class="mt-1 rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">
                        {{ $archivedLog->archived_at?->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '—' }}
                    </div>
                </div>

                {{-- created_at --}}
                <div>
                    <label for="created_at_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.created_at') }}</label>
                    <div class="mt-1 rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary">
                        {{ $archivedLog->created_at?->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '—' }}
                    </div>
                </div>

                {{-- metadata --}}
                <div class="md:col-span-2">
                    <label for="metadata_display" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('archived_logs.detail.metadata') }}</label>
                    @if($metadataJson !== null)
                        <pre class="mt-1 max-h-64 overflow-y-auto rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 font-mono text-xs text-text-primary whitespace-pre-wrap break-all shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary md:max-h-96">{{ $metadataJson }}</pre>
                    @else
                        <div class="mt-1 rounded-xl border border-ui-border bg-ui-body px-3 py-2.5 text-sm italic text-text-muted shadow-inner dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-muted">
                            {{ __('logs.detail.no_metadata') }}
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </form>

        <div class="mt-6">
            <livewire:comment-thread
                commentableType="archived-log"
                :commentableId="$archivedLog->id"
            />
        </div>

        <x-confirm-action-modal
            intent="delete"
            :action="route('archived-logs.destroy', $archivedLog->id)"
            openVar="confirmDeleteOpen"
        />
    </div>
    <!-- END: Root wrapper for Livewire single root requirement -->
</div>
