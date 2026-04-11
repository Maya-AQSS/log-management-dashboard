<div x-data="{ confirmDeleteOpen: false }">
    <!-- BEGIN: Root wrapper for Livewire single root requirement -->
    <div>
        <div class="flex min-h-[2.5rem] items-start justify-between gap-3">
        <a
            href="{{ route('error-codes.index') }}"
            class="inline-flex items-center rounded-lg border border-ui-border bg-ui-card px-4 py-2 text-sm font-semibold text-text-primary shadow-card hover:bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary dark:hover:bg-ui-dark-border"
        >
            {{ __('error_codes.buttons.back') }}
        </a>

        <div class="flex flex-1 flex-col items-center gap-1 text-center md:gap-2">
            @if ($mode === 'create')
                <h1 class="text-xl font-semibold leading-tight text-text-primary md:text-2xl dark:text-text-dark-primary">{{ __('error_codes.create_title') }}</h1>
                <p class="hidden md:block text-sm leading-snug text-text-secondary dark:text-text-dark-secondary">{{ __('error_codes.create_subtitle') }}</p>

            @else
                <h1 class="text-xl font-semibold leading-tight text-text-primary md:text-2xl dark:text-text-dark-primary">{{ __('error_codes.detail_title') }}</h1>
                <p class="hidden md:block text-sm leading-snug text-text-secondary dark:text-text-dark-secondary">
                    {{ $isEditable ? __('error_codes.edit_subtitle') : __('error_codes.detail_subtitle') }}
                </p>
            @endif
        </div>

        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
            @if ($mode === 'create')
                <a
                    href="{{ route('error-codes.index') }}"
                    class="inline-flex items-center rounded-full border border-ui-border bg-ui-card px-4 py-2 text-sm font-semibold text-text-primary hover:bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary dark:hover:bg-ui-dark-border"
                >
                    {{ __('error_codes.buttons.cancel') }}
                </a>
                <button
                    type="submit"
                    form="error-code-main-form"
                    class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-sm font-semibold text-white hover:bg-odoo-purple-d"
                >
                    {{ __('error_codes.buttons.save') }}
                </button>
            @else
                @if (! $isEditable)
                    <button
                        type="button"
                        wire:click="enableEdit"
                        class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-sm font-semibold text-white hover:bg-odoo-purple-d"
                    >
                        {{ __('error_codes.buttons.edit') }}
                    </button>
                    <button
                        type="button"
                        x-on:click="confirmDeleteOpen = true"
                        class="inline-flex items-center rounded-full bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                    >
                        {{ __('error_codes.buttons.delete') }}
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="cancelEdit"
                        class="inline-flex items-center rounded-full border border-ui-border bg-ui-card px-4 py-2 text-sm font-semibold text-text-primary hover:bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary dark:hover:bg-ui-dark-border"
                    >
                        {{ __('error_codes.buttons.cancel') }}
                    </button>
                    <button
                        type="submit"
                        form="error-code-main-form"
                        class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-sm font-semibold text-white hover:bg-odoo-purple-d"
                    >
                        {{ __('error_codes.buttons.save') }}
                    </button>
                @endif
            @endif
        </div>
        </div>

        @php
            $formActive = $mode === 'create' || $isEditable;
        @endphp

        <form
            id="error-code-main-form"
            method="POST"
            action="{{ $mode === 'create' ? route('error-codes.store') : route('error-codes.update', $errorCode->id) }}"
            class="mt-4"
        >
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
                <input type="hidden" name="errorCodeId" value="{{ $errorCode->id }}" />
            @endif

            <div
                @class([
                    'rounded-xl p-4 shadow-sm transition-colors duration-150',
                    'border border-ui-border bg-ui-card text-text-primary dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary' => ! $formActive,
                    'border-2 border-odoo-purple/45 bg-odoo-purple/[0.06] text-text-primary ring-2 ring-odoo-purple/25 dark:border-odoo-purple/50 dark:bg-odoo-purple/20 dark:text-text-dark-primary dark:ring-odoo-purple/35' => $formActive,
                ])
            >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-base">
                <!-- Fila 1: Nombre | Código de error -->
                <div>
                    <label for="name" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('error_codes.table.name') }} <span class="text-red-600">*</span></label>
                    <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $errorCode->name ?? null) }}"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-text-dark-primary',
                        'border-odoo-purple/40 bg-white text-text-primary placeholder:text-text-muted focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-text-dark-muted dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40' => $formActive,
                        'cursor-not-allowed border-ui-border bg-ui-body text-text-secondary opacity-90 dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-secondary' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                    required
                />
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('error_codes.table.code') }} <span class="text-red-600">*</span></label>
                @if($formActive)
                <input
                    id="code"
                    name="code"
                    type="text"
                    value="{{ old('code', $errorCode->code ?? null) }}"
                    class="mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-text-dark-primary border-odoo-purple/40 bg-white text-text-primary placeholder:text-text-muted focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-text-dark-muted dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40"
                    required
                />
                @else
                <div
                    id="code"
                    class="mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner cursor-not-allowed border-ui-border bg-ui-body text-text-secondary opacity-90 dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-secondary"
                >{{ $errorCode->code ?? '' }}</div>
                <input type="hidden" name="code" value="{{ old('code', $errorCode->code ?? null) }}" />
                @endif
                @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Fila 2: Aplicación | Fichero -->
            <div>
                <label for="application_id" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('error_codes.form.application') }} <span class="text-red-600">*</span></label>
                <div class="relative">
                    <select
                        id="application_id"
                        name="application_id"
                        @class([
                            'mt-1 w-full appearance-none rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-text-dark-primary pr-10',
                            'border-odoo-purple/40 bg-white text-text-primary focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40' => $formActive,
                            'cursor-not-allowed border-ui-border bg-ui-body text-text-secondary opacity-90 dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-secondary' => ! $formActive,
                        ])
                        @disabled(! $isEditable)
                        required
                    >
                        <option value="">{{ __('error_codes.filters.app_all') }}</option>
                        @foreach($applications as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('application_id', $errorCode->application_id ?? null) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-text-muted dark:text-text-dark-muted">
                        <x-chevron-down class="h-4 w-4" />
                    </span>
                </div>
                @error('application_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="file" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('error_codes.table.file') }}</label>
                <input
                    id="file"
                    name="file"
                    type="text"
                    value="{{ old('file', $errorCode->file ?? null) }}"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-text-dark-primary',
                        'border-odoo-purple/40 bg-white text-text-primary placeholder:text-text-muted focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-text-dark-muted dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40' => $formActive,
                        'cursor-not-allowed border-ui-border bg-ui-body text-text-secondary opacity-90 dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-secondary' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                />
                @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Fila 3: Línea -->
            <div></div>
            <div>
                <label for="line" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('error_codes.table.line') }}</label>
                <input
                    id="line"
                    name="line"
                    type="number"
                    min="1"
                    value="{{ old('line', $errorCode->line ?? null) }}"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-text-dark-primary',
                        'border-odoo-purple/40 bg-white text-text-primary placeholder:text-text-muted focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-text-dark-muted dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40' => $formActive,
                        'cursor-not-allowed border-ui-border bg-ui-body text-text-secondary opacity-90 dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-secondary' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                />
                @error('line')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Descripción (ancho completo) -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">{{ __('error_codes.table.description') }}</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    @class([
                        'mt-1 w-full min-w-0 rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-text-dark-primary',
                        'border-odoo-purple/40 bg-white text-text-primary placeholder:text-text-muted focus:border-odoo-purple focus:ring-odoo-purple/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-text-dark-muted dark:focus:border-odoo-purple-l dark:focus:ring-odoo-purple/40' => $formActive,
                        'cursor-not-allowed border-ui-border bg-ui-body text-text-secondary opacity-90 dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-secondary' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                >{{ old('description', $errorCode->description ?? null) }}</textarea>
                @error('description')<p class="mt-1 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900 dark:border-red-500 dark:bg-red-950 dark:!text-red-50" role="alert">{{ $message }}</p>@enderror
            </div>
            </div>
            </div>
        </form>

        @if ($mode === 'edit')
            <div class="mt-6">
                <livewire:comment-thread
                    commentableType="error-code"
                    :commentableId="$errorCode->id"
                />
            </div>

            <x-confirm-action-modal
                intent="delete"
                :action="route('error-codes.destroy', $errorCode->id)"
                openVar="confirmDeleteOpen"
            />

        @endif
    </div>
    <!-- END: Root wrapper for Livewire single root requirement -->

</div>
