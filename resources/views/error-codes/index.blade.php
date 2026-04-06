<x-layout>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-3 md:items-center">
        <div class="hidden md:block"></div>

        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-slate-100 text-center">
            {{ __('error_codes.title') }}
        </h1>

        <div class="flex justify-center md:justify-end">
            <a
                href="{{ route('error-codes.create') }}"
                class="inline-flex items-center rounded-full bg-odoo-purple px-4 py-2 text-base font-semibold text-white hover:bg-odoo-purple-d"
            >
                {{ __('error_codes.buttons.create') }}
            </a>
        </div>
    </div>

    <livewire:error-codes-table />
</x-layout>
