@php
    $title = $title ?? __('logs.filters.severity');
    $hasSelected = is_array($selected) && count($selected) > 0;
    $severities = $severities ?? \App\Enums\Severity::values();
@endphp

<details
    @if($hasSelected) open @endif
    class="group rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm dark:border-slate-700 dark:bg-slate-800"
>
    <summary class="list-none cursor-pointer select-none flex items-center justify-between gap-3 text-sm font-medium text-slate-700 dark:text-slate-200">
        <span>{{ $title }}</span>
        <x-chevron-down class="transition-transform group-open:rotate-180" />
    </summary>

    <div class="mt-2 grid grid-cols-2 gap-2">
        @foreach($severities as $severity)
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                <input
                    type="checkbox"
                    value="{{ $severity }}"
                    wire:model.defer="{{ $wireModel }}"
                    class="h-4 w-4 rounded border-slate-300 bg-white text-[#5b3853] shadow-sm focus:ring-[#5b3853]/30 dark:border-slate-700 dark:bg-slate-800"
                />
                {{ __('severity.' . $severity) }}
            </label>
        @endforeach
    </div>
</details>
