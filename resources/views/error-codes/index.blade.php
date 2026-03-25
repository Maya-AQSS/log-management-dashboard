<x-layout>
    <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-slate-100 text-center">
        {{ __('error_codes.title') }}
    </h1>

    <div class="mb-4 flex justify-end">
        <a
            href="{{ route('error-codes.create') }}"
            class="inline-flex items-center rounded-full bg-[#5b3853] px-4 py-2 text-base font-semibold text-white hover:bg-[#4a2d44]"
        >
            {{ __('error_codes.buttons.create') }}
        </a>
    </div>

    <livewire:error-codes-table />
</x-layout>
