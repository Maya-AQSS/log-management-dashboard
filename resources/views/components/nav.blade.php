<header
    x-data="{
        open: false,
        langOpen: false,
        isDark: document.documentElement.classList.contains('dark'),
        toggleDarkMode() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
    }"
    class="bg-odoo-purple dark:bg-odoo-dark-purple-d shadow-topbar"
>
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        {{-- Logo / título izquierda --}}
        <div class="flex items-center gap-2">
            <span class="text-base font-semibold tracking-wide uppercase text-text-inverse">
                {{ __('app.app_name') }}
            </span>
        </div>

        {{-- Menú centrado (oculto en mobile) --}}
        <nav class="hidden md:flex items-center gap-1">
            <a href="{{ route('dashboard') }}" class="{{ $linkClasses('dashboard*') }}">{{ __('dashboard.menu') }}</a>
            <a href="{{ route('logs.index') }}" class="{{ $linkClasses('logs*') }}">{{ __('logs.menu') }}</a>
            <a href="{{ route('archived-logs.index') }}" class="{{ $linkClasses('archived-logs*') }}">{{ __('archived_logs.menu') }}</a>
            <a href="{{ route('error-codes.index') }}" class="{{ $linkClasses('error-codes*') }}">{{ __('error_codes.menu') }}</a>
        </nav>

        {{-- Zona derecha: usuario + idioma + dark mode + logout + hamburguesa --}}
        <div class="flex items-center gap-3">
            <span class="hidden sm:inline text-sm text-text-inverse/80">
                {{ auth()->user()->name ?? __('app.nav_user') }}
            </span>

            {{-- Selector de idioma (desktop) --}}
            <div class="relative hidden sm:block">
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-sm font-semibold text-text-inverse transition-colors"
                    @click="langOpen = ! langOpen"
                >
                    <span class="sr-only">{{ __('app.nav_language') }}</span>
                    {{ strtoupper(app()->getLocale()) }}
                </button>

                <div
                    x-show="langOpen"
                    x-transition
                    @click.outside="langOpen = false"
                    class="absolute right-0 mt-2 w-40 rounded-lg bg-ui-card text-text-primary shadow-dropdown border border-ui-border-l overflow-hidden z-[300]"
                >
                    @foreach (config('app.supported_locales', ['es', 'en', 'va']) as $locale)
                        <form method="POST" action="{{ route('lang.switch', $locale) }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-ui-body {{ app()->getLocale() === $locale ? 'font-semibold text-odoo-purple' : '' }}"
                            >
                                {{ __('app.locale_'.$locale) }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            {{-- Toggle dark mode --}}
            <button
                type="button"
                class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-sm font-semibold text-text-inverse transition-colors"
                @click="toggleDarkMode()"
                :aria-pressed="isDark"
                :title="isDark ? 'Desactivar modo oscuro' : 'Activar modo oscuro'"
            >
                <span class="sr-only" x-text="isDark ? 'Desactivar modo oscuro' : 'Activar modo oscuro'"></span>
                <span x-show="!isDark" aria-hidden="true">☀️</span>
                <span x-show="isDark" aria-hidden="true">🌙</span>
            </button>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                @csrf
                <button
                    type="submit"
                    class="px-3 py-1.5 rounded-full bg-warning hover:bg-warning-dark text-sm font-semibold text-text-primary shadow-card transition-colors"
                >
                    {{ __('app.nav_logout') }}
                </button>
            </form>

            {{-- Menú móvil --}}
            <button
                type="button"
                class="md:hidden inline-flex items-center justify-center p-2 rounded-full hover:bg-white/10 text-text-inverse"
                @click="open = ! open"
            >
                <span class="sr-only">{{ __('app.nav_open_menu') }}</span>
                ☰
            </button>
        </div>
    </div>

    {{-- Menú móvil desplegable --}}
    <nav
        x-show="open"
        x-transition
        class="md:hidden border-t border-white/10 bg-odoo-purple-d dark:bg-odoo-dark-purple-d"
    >
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block {{ $linkClasses('dashboard*') }}">{{ __('dashboard.menu') }}</a>
            <a href="{{ route('logs.index') }}" class="block {{ $linkClasses('logs*') }}">{{ __('logs.menu') }}</a>
            <a href="{{ route('archived-logs.index') }}" class="block {{ $linkClasses('archived-logs*') }}">{{ __('archived_logs.menu') }}</a>
            <a href="{{ route('error-codes.index') }}" class="block {{ $linkClasses('error-codes*') }}">{{ __('error_codes.menu') }}</a>

            {{-- Selector de idioma (mobile) --}}
            <div class="pt-2 border-t border-white/10">
                <div class="text-xs uppercase tracking-wide text-text-inverse/70 mb-2">
                    {{ __('app.nav_language') }}
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach (config('app.supported_locales', ['es', 'en', 'va']) as $locale)
                        <form method="POST" action="{{ route('lang.switch', $locale) }}">
                            @csrf
                            <button
                                type="submit"
                                class="px-3 py-1.5 rounded-full text-sm font-semibold text-text-inverse {{ app()->getLocale() === $locale ? 'bg-white/20' : 'bg-white/10 hover:bg-white/15' }}"
                            >
                                {{ __('app.locale_'.$locale) }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>
</header>
