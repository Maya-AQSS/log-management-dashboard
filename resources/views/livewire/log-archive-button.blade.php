<div>
    @if(!$archived)
        <button
            type="button"
            wire:click="archive"
            class="inline-flex items-center px-4 py-2 rounded-full bg-[#5b3853] hover:bg-[#4a2d44] text-white text-sm font-semibold"
        >
            {{ __('logs.buttons.archive') }}
        </button>
    @else
        <a
            href="{{ route('archived-logs.show', $archivedLogId) }}"
            class="inline-flex items-center px-4 py-2 rounded-full bg-[#5b3853] hover:bg-[#4a2d44] text-white text-sm font-semibold"
        >
            {{ __('logs.buttons.edit') }}
        </a>
    @endif
</div>

