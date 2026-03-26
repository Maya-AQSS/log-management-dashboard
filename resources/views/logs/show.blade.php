<x-layout>
    <div class="flex items-start justify-between gap-3">
        <a
            href="{{ $backHref }}"
            class="inline-flex items-center rounded-full bg-[#f7a736] px-4 py-2 text-sm font-semibold text-[#1e1a24] shadow-sm hover:bg-[#e28f1f] dark:bg-amber-500 dark:hover:bg-amber-400"
        >
            {{ __('logs.buttons.back') }}
        </a>

        <div class="text-center">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                {{ $source === 'archived_log' ? __('archived_logs.title') : __('logs.detail.title') }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">#{{ $source === 'archived_log' ? $archivedLog->id : $log->id }}</p>
        </div>

        <div class="flex items-center">
            @if($source === 'archived_log')
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('archived-logs.show', $archivedLog->id) }}"
                        class="inline-flex items-center px-4 py-2 rounded-full bg-[#5b3853] hover:bg-[#4a2d44] text-white text-sm font-semibold"
                    >
                        {{ __('archived_logs.buttons.edit') }}
                    </a>

                    @can('delete', $archivedLog)
                        <form
                            method="POST"
                            action="{{ route('archived-logs.destroy', $archivedLog->id) }}"
                            onsubmit="return confirm('{{ addslashes(__('archived_logs.confirm_delete')) }}')"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="px-3 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-base font-semibold text-white shadow-sm"
                            >
                                {{ __('archived_logs.buttons.delete') }}
                            </button>
                        </form>
                    @endcan
                </div>
            @else
                <div class="flex items-center gap-2">
                    @if($archivedLogId === null)
                        <form method="POST" action="{{ route('logs.archive', $log->id) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                            >
                                {{ __('logs.buttons.archive') }}
                            </button>
                        </form>
                    @endif

                    @if(!$log->resolved)
                        <form method="POST" action="{{ route('logs.resolve', $log->id) }}">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#4a2d44]"
                            >
                                {{ __('logs.buttons.solved') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <livewire:log-detail
        :source="$source"
        :record-id="$source === 'archived_log' ? $archivedLog->id : $log->id"
    />

    @if($source === 'archived_log')
        <div class="mt-6">
            <livewire:comment-thread
                commentableType="archived-log"
                :commentableId="$archivedLog->id"
            />
        </div>
    @endif
</x-layout>
