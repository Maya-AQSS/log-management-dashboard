<x-layout>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-3 md:items-center">
        <div class="hidden md:block"></div>

        <h1 class="text-3xl font-bold text-text-primary dark:text-text-dark-primary md:text-4xl text-center">
            {{ __('error_codes.title') }}
        </h1>

        <div class="flex justify-center md:justify-end">
            <a
                href="{{ route('error-codes.create') }}"
                class="inline-flex items-center bg-odoo-purple dark:bg-odoo-dark-purple text-text-inverse border-odoo-purple dark:border-odoo-dark-purple hover:bg-odoo-purple-d dark:hover:bg-odoo-dark-purple-d hover:border-odoo-purple-d dark:hover:border-odoo-dark-purple-d px-4 py-1.5 rounded-md text-sm font-semibold transition-colors cursor-pointer border"
            >
                {{ __('error_codes.buttons.create') }}
            </a>
        </div>
    </div>

    <livewire:error-codes-table />
</x-layout>
