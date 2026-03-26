@props([
    'class' => '',
])

<svg
    {{ $attributes->merge(['class' => trim('h-4 w-4 text-slate-500 dark:text-slate-300 ' . $class)]) }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
    fill="none"
    aria-hidden="true"
>
    <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
</svg>
