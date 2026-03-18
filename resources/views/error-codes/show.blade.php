<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('error_codes.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-5">
        {{ __('error_codes.welcome') }}
    </p>

    <div class="mt-4 bg-white border rounded-2xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <div class="font-semibold">{{ __('error_codes.table.application') }}</div>
                <div class="text-slate-700">{{ $errorCode->application?->name ?? '-' }}</div>
            </div>

            <div>
                <div class="font-semibold">{{ __('error_codes.table.severity') }}</div>
                <div class="text-slate-700">{{ $errorCode->severity ?? '-' }}</div>
            </div>

            <div>
                <div class="font-semibold">{{ __('error_codes.table.code') }}</div>
                <div class="text-slate-700 whitespace-nowrap">{{ $errorCode->code ?? '-' }}</div>
            </div>

            <div>
                <div class="font-semibold">{{ __('error_codes.table.name') }}</div>
                <div class="text-slate-700">{{ $errorCode->name ?? '-' }}</div>
            </div>

            <div class="md:col-span-2">
                <div class="font-semibold">{{ __('error_codes.table.description') }}</div>
                <div class="text-slate-700 break-words">{{ $errorCode->description ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <livewire:comment-thread
            commentableType="error-code"
            :commentableId="$errorCode->id"
        />
    </div>

    <div class="mt-4 text-center">
        <a
            href="{{ route('error-codes.index') }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold"
        >
            {{ __('error_codes.buttons.back') }}
        </a>
    </div>
</x-layout>
