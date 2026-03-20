<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('logs.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-5">
        {{ __('logs.welcome') }}
    </p>

    <div class="mt-4 bg-white border rounded-2xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <div class="font-semibold">{{ __('logs.table.application') }}</div>
                <div class="text-slate-700">{{ $log->application?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="font-semibold">{{ __('logs.table.severity') }}</div>
                <div class="text-slate-700">
                    <x-severity-badge :severity="$log->severity" />
                </div>
            </div>
            <div class="md:col-span-2">
                <div class="font-semibold">{{ __('logs.table.message') }}</div>
                <div class="text-slate-700 break-words">{{ $log->message ?? '-' }}</div>
            </div>
            <div>
                <div class="font-semibold">{{ __('logs.table.error_code') }}</div>
                <div class="text-slate-700">{{ $log->errorCode?->code ?? '-' }}</div>
            </div>
            <div>
                <div class="font-semibold">{{ __('logs.table.created_at') }}</div>
                <div class="text-slate-700">
                    {{ optional($log->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a
            href="{{ route('logs.index') }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold"
        >
            Back
        </a>
    </div>

    <div class="mt-4 text-center">
        <livewire:log-archive-button :logId="$log->id" />
    </div>
</x-layout>
