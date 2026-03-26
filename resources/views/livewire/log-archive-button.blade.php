<div>
    @if(!$archived)
        <button
            type="button"
            wire:click="archive"
            class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
        >
            {{ __('logs.buttons.archive') }}
        </button>
    @else
        <a
            href="{{ route('archived-logs.show', $archivedLogId) }}"
            class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4a2d44]"
        >
            {{ __('logs.buttons.archive') }}
        </a>
    @endif
</div>

