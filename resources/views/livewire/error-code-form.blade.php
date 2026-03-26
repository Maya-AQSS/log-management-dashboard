<div x-data="{ confirmDeleteOpen: false }">
    <div class="flex min-h-[2.5rem] items-start justify-between gap-3">
        <a
            href="{{ route('error-codes.index') }}"
            class="inline-flex items-center rounded-full bg-[#f7a736] px-4 py-2 text-sm font-semibold text-[#1e1a24] shadow-sm hover:bg-[#e28f1f] dark:bg-amber-500 dark:hover:bg-amber-400"
        >
            {{ __('error_codes.buttons.back') }}
        </a>

        <div class="flex flex-1 flex-col items-center gap-1 text-center md:gap-2">
            @if ($mode === 'create')
                <h1 class="text-xl font-semibold leading-tight text-slate-900 md:text-2xl dark:text-slate-100">{{ __('error_codes.create_title') }}</h1>
                <p class="text-sm leading-snug text-slate-600 dark:text-slate-400">{{ __('error_codes.create_subtitle') }}</p>
            @else
                <h1 class="text-xl font-semibold leading-tight text-slate-900 md:text-2xl dark:text-slate-100">{{ __('error_codes.title') }}</h1>
                <p class="text-sm leading-snug text-slate-600 dark:text-slate-400">
                    {{ $isEditable ? __('error_codes.edit_subtitle') : __('error_codes.detail_subtitle') }}
                </p>
            @endif
        </div>

        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
            @if ($mode === 'create')
                <a
                    href="{{ route('error-codes.index') }}"
                    class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    {{ __('error_codes.buttons.cancel') }}
                </a>
                <button
                    type="submit"
                    form="error-code-main-form"
                    class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
                >
                    {{ __('error_codes.buttons.save') }}
                </button>
            @else
                @if (! $isEditable)
                    <button
                        type="button"
                        wire:click="enableEdit"
                        class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
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
                        class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        {{ __('error_codes.buttons.cancel') }}
                    </button>
                    <button
                        type="submit"
                        form="error-code-main-form"
                        class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
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
                'border border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100' => ! $formActive,
                'border-2 border-[#5b3853]/45 bg-[#5b3853]/[0.06] text-slate-900 ring-2 ring-[#5b3853]/25 dark:border-[#5b3853]/50 dark:bg-[#5b3853]/20 dark:text-slate-100 dark:ring-[#5b3853]/35' => $formActive,
            ])
        >
        <div class="grid grid-cols-1 gap-3 text-base md:grid-cols-2">
            <div>
                <label for="application_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.form.application') }}</label>
                <select
                    id="application_id"
                    name="application_id"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-slate-100',
                        'border-[#5b3853]/40 bg-white text-slate-900 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => $formActive,
                        'cursor-not-allowed border-slate-200 bg-white text-slate-700 opacity-90 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-300' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                    required
                >
                    <option value="">{{ __('error_codes.filters.app_all') }}</option>
                    @foreach($applications as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('application_id', $errorCode->application_id ?? null) === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                @error('application_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="severity" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.table.severity') }}</label>
                <select
                    id="severity"
                    name="severity"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-slate-100',
                        'border-[#5b3853]/40 bg-white text-slate-900 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => $formActive,
                        'cursor-not-allowed border-slate-200 bg-white text-slate-700 opacity-90 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-300' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                >
                    <option value="">-</option>
                    <option value="critical" @selected(old('severity', $errorCode->severity ?? null) === 'critical')>{{ __('severity.critical') }}</option>
                    <option value="high" @selected(old('severity', $errorCode->severity ?? null) === 'high')>{{ __('severity.high') }}</option>
                    <option value="medium" @selected(old('severity', $errorCode->severity ?? null) === 'medium')>{{ __('severity.medium') }}</option>
                    <option value="low" @selected(old('severity', $errorCode->severity ?? null) === 'low')>{{ __('severity.low') }}</option>
                    <option value="other" @selected(old('severity', $errorCode->severity ?? null) === 'other')>{{ __('severity.other') }}</option>
                </select>
                @error('severity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.table.code') }}</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    value="{{ old('code', $errorCode->code ?? null) }}"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-slate-100',
                        'border-[#5b3853]/40 bg-white text-slate-900 placeholder:text-slate-400 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-slate-500 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => $formActive,
                        'cursor-not-allowed border-slate-200 bg-white text-slate-700 opacity-90 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-300' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                    required
                />
                @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.table.name') }}</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $errorCode->name ?? null) }}"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-slate-100',
                        'border-[#5b3853]/40 bg-white text-slate-900 placeholder:text-slate-400 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-slate-500 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => $formActive,
                        'cursor-not-allowed border-slate-200 bg-white text-slate-700 opacity-90 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-300' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                    required
                />
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="file" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.table.file') }}</label>
                <input
                    id="file"
                    name="file"
                    type="text"
                    value="{{ old('file', $errorCode->file ?? null) }}"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-slate-100',
                        'border-[#5b3853]/40 bg-white text-slate-900 placeholder:text-slate-400 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-slate-500 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => $formActive,
                        'cursor-not-allowed border-slate-200 bg-white text-slate-700 opacity-90 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-300' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                />
                @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="line" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.table.line') }}</label>
                <input
                    id="line"
                    name="line"
                    type="number"
                    min="1"
                    value="{{ old('line', $errorCode->line ?? null) }}"
                    @class([
                        'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-slate-100',
                        'border-[#5b3853]/40 bg-white text-slate-900 placeholder:text-slate-400 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-slate-500 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => $formActive,
                        'cursor-not-allowed border-slate-200 bg-white text-slate-700 opacity-90 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-300' => ! $formActive,
                    ])
                    @disabled(! $isEditable)
                />
                @error('line')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.table.description') }}</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    @class([
                        'mt-1 w-full min-w-0 rounded-xl border px-3 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 dark:text-slate-100',
                        'border-[#5b3853]/40 bg-white text-slate-900 placeholder:text-slate-400 focus:border-[#5b3853] focus:ring-[#5b3853]/25 dark:border-slate-500 dark:bg-slate-950 dark:placeholder:text-slate-500 dark:focus:border-[#c4a8bc] dark:focus:ring-[#5b3853]/40' => $formActive,
                        'cursor-not-allowed border-slate-200 bg-white text-slate-700 opacity-90 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-300' => ! $formActive,
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

        <x-confirm-delete-modal
            :action="route('error-codes.destroy', $errorCode->id)"
            openVar="confirmDeleteOpen"
        />
    @endif
</div>
