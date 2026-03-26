<div x-data="{ confirmDeleteOpen: false }">
    <div class="flex items-start justify-between gap-3">
        <a
            href="{{ route('error-codes.index') }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-[#f7a736] hover:bg-[#e28f1f] text-[#1e1a24] text-sm font-semibold shadow-sm"
        >
            {{ __('error_codes.buttons.back') }}
        </a>

        <div class="text-center">
            @if ($mode === 'create')
                <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('error_codes.create_title') }}</h1>
                <p class="text-base text-gray-500 dark:text-slate-400">{{ __('error_codes.create_subtitle') }}</p>
            @else
                <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('error_codes.title') }}</h1>
                <p class="text-base text-gray-500 dark:text-slate-400">
                    {{ $isEditable ? __('error_codes.edit_subtitle') : __('error_codes.detail_subtitle') }}
                </p>
            @endif
        </div>

        <div class="w-20"></div>
    </div>

    <form
        method="POST"
        action="{{ $mode === 'create' ? route('error-codes.store') : route('error-codes.update', $errorCode->id) }}"
        class="mt-4 bg-white border border-slate-200 rounded-2xl p-4 space-y-4 dark:bg-slate-900 dark:border-slate-700"
    >
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
            <input type="hidden" name="errorCodeId" value="{{ $errorCode->id }}" />
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-base">
            <div>
                <label for="application_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('error_codes.form.application') }}</label>
                <select
                    id="application_id"
                    name="application_id"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
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
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                    @disabled(! $isEditable)
                >{{ old('description', $errorCode->description ?? null) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-2 justify-end flex-wrap">
            @if ($mode === 'create')
                <a
                    href="{{ route('error-codes.index') }}"
                    class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    {{ __('error_codes.buttons.cancel') }}
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
                >
                    {{ __('error_codes.buttons.save') }}
                </button>
            @else
                @if (! $isEditable)
                    <button
                        type="button"
                        wire:click="enableEdit"
                        class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        {{ __('error_codes.buttons.edit') }}
                    </button>
                @endif

                @if ($isEditable)
                    <button
                        type="button"
                        wire:click="cancelEdit"
                        class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        {{ __('error_codes.buttons.cancel') }}
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
                    >
                        {{ __('error_codes.buttons.save') }}
                    </button>
                @endif

                <button
                    type="button"
                    x-on:click="confirmDeleteOpen = true"
                    class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-2 text-base font-semibold text-red-700 hover:bg-red-100"
                >
                    {{ __('error_codes.buttons.delete') }}
                </button>
            @endif
        </div>
    </form>

    @if ($mode === 'edit')
        <div class="mt-6">
            <livewire:comment-thread
                commentableType="error-code"
                :commentableId="$errorCode->id"
            />
        </div>

        <div
            x-cloak
            x-show="confirmDeleteOpen"
            x-on:keydown.escape.window="confirmDeleteOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="absolute inset-0 bg-black/50" x-on:click="confirmDeleteOpen = false"></div>

            <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('error_codes.buttons.delete') }}</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('error_codes.messages.delete_confirm') }}</p>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        x-on:click="confirmDeleteOpen = false"
                        class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        {{ __('error_codes.buttons.cancel') }}
                    </button>

                    <form method="POST" action="{{ route('error-codes.destroy', $errorCode->id) }}">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                        >
                            {{ __('error_codes.buttons.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
