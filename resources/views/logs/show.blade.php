<x-layout>
    <div class="flex items-start justify-between gap-3">
        <a
            href="{{ route('logs.index') }}"
            class="inline-flex items-center rounded-full bg-[#f7a736] px-4 py-2 text-sm font-semibold text-[#1e1a24] shadow-sm hover:bg-[#e28f1f] dark:bg-amber-500 dark:hover:bg-amber-400"
        >
            {{ __('logs.buttons.back') }}
        </a>

        <div class="text-center">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('logs.detail.title') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">#{{ $log->id }}</p>
        </div>

        <div class="flex items-center">
            <livewire:log-archive-button :logId="$log->id" />
        </div>
    </div>

    <livewire:log-detail :log-id="$log->id" />
</x-layout>
