<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name', 'Log Dashboard') }}</title>

    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <x-nav />

    <div class="max-w-6xl mx-auto px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-emerald-100 text-emerald-800 px-4 py-2 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <main class="bg-white rounded-2xl shadow-md p-4 md:p-6 border border-[#e2d8eb]">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>