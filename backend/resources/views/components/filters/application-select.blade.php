<div>
    @if(!$hideLabel && filled($label))
        <label class="mb-1 block text-sm font-medium text-text-secondary dark:text-text-dark-secondary">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <select
            {{ $attributes->class('w-full appearance-none rounded-lg border border-ui-border bg-ui-card px-3 py-2 pr-10 text-base shadow-sm dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary focus:border-odoo-purple focus:outline-none focus:ring-2 focus:ring-odoo-purple/20') }}
        >
            <option value="">{{ $placeholder }}</option>
            @foreach($applications as $id => $name)
                <option value="{{ $id }}" @selected((string) $selected === (string) $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>

        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-text-muted dark:text-text-dark-muted">
            <x-chevron-down class="h-4 w-4" />
        </span>
    </div>
</div>
