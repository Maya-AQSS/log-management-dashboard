<x-layout>
    <h1 class="text-xl font-semibold text-center">{{ __('error_codes.title') }}</h1>
    <p class="text-base text-gray-500 text-center mb-5">{{ __('error_codes.welcome') }}</p>

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
