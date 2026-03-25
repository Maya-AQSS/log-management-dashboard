@props([
    'label',
    'placeholder',
    'applications' => [],
])

<div>
    <label class="block text-sm font-medium text-slate-700">
        {{ $label }}
    </label>

    <select
        {{ $attributes->class('mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-base shadow-sm focus:border-[#5b3853] focus:outline-none focus:ring-2 focus:ring-[#5b3853]/20') }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($applications as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
    </select>
</div>
