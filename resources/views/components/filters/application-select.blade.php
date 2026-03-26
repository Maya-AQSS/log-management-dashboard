<div>
    @if(!$hideLabel && filled($label))
        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <select
            {{ $attributes->class('w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-base shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20') }}
        >
            <option value="">{{ $placeholder }}</option>
            @foreach($applications as $id => $name)
                <option value="{{ $id }}" @selected((string) $selected === (string) $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>

        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-500 dark:text-slate-300">
            <x-chevron-down class="h-4 w-4" />
        </span>
    </div>
</div>
