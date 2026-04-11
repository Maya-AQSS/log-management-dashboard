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
    <div class="flex items-center gap-4 relative">
        @php
            $initial = strtoupper(substr(auth()->user()->name ?? 'U', 0, 1));
        @endphp

        {{-- Toggle dark mode --}}
        <button
            type="button"
            class="p-2 rounded-lg hover:bg-ui-body dark:hover:bg-ui-dark-card text-text-secondary dark:text-text-dark-secondary transition-colors"
            data-dark-mode-toggle
            @click="toggleDarkMode()"
            :aria-pressed="isDark"
            :title="isDark ? 'Desactivar modo oscuro' : 'Activar modo oscuro'"
        >
            <span class="sr-only" x-text="isDark ? 'Desactivar modo oscuro' : 'Activar modo oscuro'"></span>
            <svg x-show="isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true" style="display: none;">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
            </svg>
            <svg x-show="!isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
        </button>

        {{-- Dropdown de Usuario / Idioma / Logout --}}
        <div class="relative">
            <button
                type="button"
                data-user-menu-trigger
                @click="userMenuOpen = !userMenuOpen"
                class="w-8 h-8 rounded-full bg-odoo-purple flex items-center justify-center text-xs font-bold text-white transition-colors"
            >
                {{ $initial }}
            </button>

            <div
                x-show="userMenuOpen"
                x-transition
                @click.outside="userMenuOpen = false"
                data-user-menu-dropdown
                class="absolute right-0 mt-2 w-48 rounded-lg bg-ui-card dark:bg-ui-dark-card shadow-dropdown border border-ui-border dark:border-ui-dark-border py-1 z-[300]"
                style="display: none;"
            >
                <div class="px-4 py-2 border-b border-ui-border dark:border-ui-dark-border mb-1">
                    <p class="text-sm font-medium text-text-primary dark:text-text-dark-primary truncate">
                        {{ auth()->user()->name ?? __('app.nav_user') }}
                    </p>
                </div>
                
                {{-- Selector de Idioma (integrado al menú) --}}
                <div class="px-3 py-1">
                    <p class="text-xs font-semibold text-text-muted dark:text-text-dark-muted mb-1 px-1 uppercase tracking-wider">Idioma</p>
                    <div class="flex gap-1">
                        @foreach (config('app.supported_locales', ['es', 'en', 'va']) as $locale)
                            <form method="POST" action="{{ route('lang.switch', $locale) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full text-center px-1.5 py-1 text-xs rounded hover:bg-ui-body dark:hover:bg-ui-dark-border {{ app()->getLocale() === $locale ? 'bg-ui-body dark:bg-ui-dark-border font-semibold text-odoo-purple dark:text-odoo-dark-purple' : 'text-text-secondary dark:text-text-dark-secondary' }}">
                                    {{ strtoupper($locale) }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-ui-border dark:border-ui-dark-border mt-1"></div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button
                        type="submit"
                        class="w-full text-left px-4 py-2 text-sm text-danger dark:text-danger-light hover:bg-danger/10 transition-colors"
                    >
                        {{ __('app.nav_logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
