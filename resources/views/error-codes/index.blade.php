<x-layout>
    <div class="w-full mt-6 flex items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">
            {{ __('error_codes.title') }}
        </h1>

        <button
            type="button"
            class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
            disabled
        >
            {{ __('error_codes.buttons.create') }}
        </button>
    </div>

    <livewire:error-codes-table />
</x-layout>
