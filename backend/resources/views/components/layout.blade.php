<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('app.app_name') }}</title>

    {{-- Dark mode: aplicar antes de que el navegador pinte --}}
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = savedTheme ? savedTheme === 'dark' : prefersDark;
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/tiptap-editor.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-ui-body text-text-primary font-sans antialiased dark:bg-ui-dark-bg dark:text-text-dark-primary">

    <x-sidebar />

    <div class="ml-64 flex flex-col min-h-screen">
        <x-topbar />

        <main class="flex-1 px-6 py-3">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-success-light text-success-dark dark:bg-success-dark/30 dark:text-success-light px-4 py-2 shadow-card text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
