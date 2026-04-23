<header
    x-data="{
        langOpen: false,
        userMenuOpen: false,
        isDark: document.documentElement.classList.contains('dark'),
        toggleDarkMode() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
    }"
    class="h-14 bg-ui-topbar dark:bg-ui-dark-topbar shadow-topbar flex items-center justify-between px-6 z-[200] shrink-0 border-b border-ui-border dark:border-ui-dark-border"
>
    {{-- Título de página --}}
    <h1 class="text-md font-semibold text-text-primary dark:text-text-dark-primary truncate" data-topbar-title>
        {{ $pageTitle }}
    </h1>

    {{-- Controles derecha --}}
    <div class="flex items-center gap-3">
        @php
            $userName = auth()->user()->name ?? __('app.nav_user');
            $initial  = strtoupper(substr($userName, 0, 1));
        @endphp

        {{-- Selector de idioma --}}
        <div class="flex gap-0.5">
            @foreach (config('app.supported_locales', ['es', 'en', 'va']) as $locale)
                <form method="POST" action="{{ route('lang.switch', $locale) }}">
                    @csrf
                    <button type="submit" class="px-1.5 py-0.5 text-xs rounded hover:bg-ui-body dark:hover:bg-ui-dark-card transition-colors {{ app()->getLocale() === $locale ? 'font-semibold text-odoo-purple dark:text-odoo-dark-purple' : 'text-text-secondary dark:text-text-dark-secondary' }}">
                        {{ strtoupper($locale) }}
                    </button>
                </form>
            @endforeach
        </div>

        {{-- Toggle dark mode --}}
        <button
            type="button"
            class="p-2 rounded-lg hover:bg-ui-body dark:hover:bg-ui-dark-card text-text-secondary dark:text-text-dark-secondary transition-colors"
            @click="toggleDarkMode()"
            :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        >
            <svg x-show="isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-yellow-400" aria-hidden="true" style="display: none;">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
            </svg>
            <svg x-show="!isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-gray-600" aria-hidden="true">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
        </button>

        {{-- Nombre del usuario --}}
        <span class="text-text-primary dark:text-text-dark-primary font-medium hidden sm:inline">{{ $userName }}</span>

        {{-- Avatar --}}
        <div class="w-8 h-8 rounded-full bg-odoo-purple flex items-center justify-center">
            <span class="text-xs font-bold text-white">{{ $initial }}</span>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="border border-ui-border dark:border-ui-dark-border text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary hover:border-text-secondary dark:hover:border-text-dark-secondary px-3 py-1 rounded text-sm transition-colors cursor-pointer bg-transparent"
            >
                {{ __('app.nav_logout') }}
            </button>
        </form>
    </div>
</header>
