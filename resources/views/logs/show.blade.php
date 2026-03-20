<x-layout>
    <div class="flex items-start justify-between gap-3">
        <a
            href="{{ route('logs.index') }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-[#f7a736] hover:bg-[#e28f1f] text-[#1e1a24] text-sm font-semibold shadow-sm"
        >
            Back
        </a>

        <div class="text-center">
            <h1 class="text-xl font-semibold">{{ __('logs.title') }}</h1>
        </div>

        <div class="flex items-center">
            <livewire:log-archive-button :logId="$log->id" />
        </div>
    </div>

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

</x-layout>
