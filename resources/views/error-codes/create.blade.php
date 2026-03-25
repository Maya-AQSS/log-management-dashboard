<x-layout>
    <div class="flex items-start justify-between gap-3">
        <a
            href="{{ route('error-codes.index') }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-[#f7a736] hover:bg-[#e28f1f] text-[#1e1a24] text-sm font-semibold shadow-sm"
        >
            {{ __('error_codes.buttons.back') }}
        </a>

        <div class="text-center">
            <h1 class="text-xl font-semibold">{{ __('error_codes.create_title') }}</h1>
            <p class="text-base text-gray-500">{{ __('error_codes.create_subtitle') }}</p>
        </div>

        <div class="w-20"></div>
    </div>

    <form method="POST" action="{{ route('error-codes.store') }}" class="mt-4 bg-white border rounded-2xl p-4 space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-base">
            <div>
                <label for="application_id" class="block text-sm font-medium text-slate-700">{{ __('error_codes.form.application') }}</label>
                <select
                    id="application_id"
                    name="application_id"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                    required
                >
                    <option value="">{{ __('error_codes.filters.app_all') }}</option>
                    @foreach($applications as $id => $name)
                        <option value="{{ $id }}" @selected(old('application_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
                @error('application_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="severity" class="block text-sm font-medium text-slate-700">{{ __('error_codes.table.severity') }}</label>
                <select
                    id="severity"
                    name="severity"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                >
                    <option value="">-</option>
                    <option value="critical" @selected(old('severity') === 'critical')>{{ __('severity.critical') }}</option>
                    <option value="high" @selected(old('severity') === 'high')>{{ __('severity.high') }}</option>
                    <option value="medium" @selected(old('severity') === 'medium')>{{ __('severity.medium') }}</option>
                    <option value="low" @selected(old('severity') === 'low')>{{ __('severity.low') }}</option>
                    <option value="other" @selected(old('severity') === 'other')>{{ __('severity.other') }}</option>
                </select>
                @error('severity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-slate-700">{{ __('error_codes.table.code') }}</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    value="{{ old('code') }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                    required
                />
                @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">{{ __('error_codes.table.name') }}</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                    required
                />
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="file" class="block text-sm font-medium text-slate-700">{{ __('error_codes.table.file') }}</label>
                <input
                    id="file"
                    name="file"
                    type="text"
                    value="{{ old('file') }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                />
                @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="line" class="block text-sm font-medium text-slate-700">{{ __('error_codes.table.line') }}</label>
                <input
                    id="line"
                    name="line"
                    type="number"
                    min="1"
                    value="{{ old('line') }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                />
                @error('line')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700">{{ __('error_codes.table.description') }}</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20"
                >{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <a
                href="{{ route('error-codes.index') }}"
                class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50"
            >
                {{ __('error_codes.buttons.cancel') }}
            </a>
            <button
                type="submit"
                class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
            >
                {{ __('error_codes.buttons.save') }}
            </button>
        </div>
    </form>
</x-layout>
