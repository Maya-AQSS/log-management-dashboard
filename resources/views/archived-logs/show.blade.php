<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('archived_logs.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-5">
        {{ __('archived_logs.welcome') }}
    </p>

    <div class="mt-4 bg-white border rounded-2xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <div class="font-semibold">{{ __('archived_logs.table.application') }}</div>
                <div class="text-slate-700">{{ $archivedLog->application?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="font-semibold">{{ __('archived_logs.table.severity') }}</div>
                <div class="text-slate-700">{{ $archivedLog->severity ?? '-' }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="font-semibold">{{ __('archived_logs.table.message') }}</div>
                <div class="text-slate-700 break-words">{{ $archivedLog->message ?? '-' }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="font-semibold">{{ __('archived_logs.table.archived_at') }}</div>
                <div class="text-slate-700">
                    {{ optional($archivedLog->archived_at)->toDateTimeString() ?? '-' }}
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-end gap-3">
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
    </div>

    <div class="mt-4 text-center">
        <a
            href="{{ route('archived-logs.index') }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold"
        >
            Back
        </a>
    </div>
</x-layout>
