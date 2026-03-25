<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ __('app.app_name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/tiptap-editor.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <x-nav />

    <div class="max-w-6xl mx-auto px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200 px-4 py-2 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <main class="bg-white dark:bg-slate-900 rounded-2xl shadow-md p-4 md:p-6 border border-[#e2d8eb] dark:border-slate-700">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>