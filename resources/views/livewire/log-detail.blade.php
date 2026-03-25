<div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
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
</div>
