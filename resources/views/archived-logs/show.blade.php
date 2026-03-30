<x-layout>
    <div class="flex items-start justify-between gap-3" x-data="{ confirmDeleteArchivedOpen: false }">
        <a
            href="{{ route('archived-logs.index') }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-[#f7a736] hover:bg-[#e28f1f] text-[#1e1a24] text-sm font-semibold shadow-sm"
        >
            Back
        </a>

        <div class="text-center">
            <h1 class="text-xl font-semibold">{{ __('archived_logs.title') }}</h1>
            <p class="text-base text-gray-500">
                {{ __('archived_logs.welcome') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('archived-logs.show', $archivedLog->id) }}"
                class="inline-flex items-center px-4 py-2 rounded-full bg-[#5b3853] hover:bg-[#4a2d44] text-white text-sm font-semibold"
            >
                {{ __('archived_logs.buttons.edit') }}
            </a>
            @can('delete', $archivedLog)
                <button
                    type="button"
                    x-on:click="confirmDeleteArchivedOpen = true"
                    class="px-3 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-base font-semibold text-white shadow-sm"
                >
                    {{ __('archived_logs.buttons.delete') }}
                </button>

                <x-confirm-action-modal
                    intent="delete_archived"
                    :action="route('archived-logs.destroy', $archivedLog->id)"
                    openVar="confirmDeleteArchivedOpen"
                />
            @endcan
        </div>
    </div>

    <div class="mt-4 bg-white border rounded-2xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <div class="font-semibold">{{ __('archived_logs.table.application') }}</div>
                <div class="text-slate-700">{{ $archivedLog->application?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="font-semibold">{{ __('archived_logs.table.severity') }}</div>
                <div class="text-slate-700">
                    <x-severity-badge :severity="$archivedLog->severity" />
                </div>
            </div>
            <div class="md:col-span-2">
                <div class="font-semibold">{{ __('archived_logs.table.message') }}</div>
                <div class="text-slate-700 break-words">{{ $archivedLog->message ?? '-' }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="font-semibold">{{ __('archived_logs.table.archived_at') }}</div>
                <div class="text-slate-700">
                    {{ optional($archivedLog->archived_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i:s') ?? '-' }}
                </div>
            </div>

            @if(!blank($archivedLog->url_tutorial))
                <div class="md:col-span-2">
                    <a
                        href="{{ $archivedLog->url_tutorial }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-4 py-2 rounded-full bg-[#5b3853] hover:bg-[#4a2d44] text-white text-sm font-semibold shadow-sm"
                    >
                        {{ __('archived_logs.buttons.view_tutorial') }}
                    </a>
                </div>
            @endif
        </div>

    </div>

    <div class="mt-6">
        <livewire:comment-thread
            commentableType="archived-log"
            :commentableId="$archivedLog->id"
        />
    </div>
</x-layout>
