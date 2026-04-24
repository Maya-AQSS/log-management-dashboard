<header
    x-data="{
        open: false,
        langOpen: false,
        favOpen: false,
        favorites: [],
        favError: false,
        isDark: document.documentElement.classList.contains('dark'),
        toggleDarkMode() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
        getCookie(name) {
            return document.cookie.split('; ').find(r => r.startsWith(name + '='))?.split('=')[1];
        },
        parseJwtSub(token) {
            try {
                const payload = token.split('.')[1];
                const json = JSON.parse(atob(payload.replace(/-/g,'+').replace(/_/g,'/')));
                return json.sub;
            } catch { return null; }
        },
        async loadFavorites() {
            this.favError = false;
            const token = this.getCookie('session_token');
            if (!token) { this.favorites = []; this.favError = true; return; }
            const sub = this.parseJwtSub(token);
            if (!sub) { this.favorites = []; this.favError = true; return; }
            try {
                const url = `http://maya_dashboard_api.localhost/api/v1/dashboard/user/${encodeURIComponent(sub)}/favorites`;
                const r = await fetch(url, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } });
                if (!r.ok) { this.favError = true; this.favorites = []; return; }
                const p = await r.json();
                this.favorites = Array.isArray(p) ? p : (p?.data ?? []);
            } catch { this.favError = true; this.favorites = []; }
        },
        init() {
            this.loadFavorites();
            window.addEventListener('storage', (e) => { if (e.key === 'maya:favorites-updated-at') this.loadFavorites(); });
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

            {{-- Favoritas (desktop) --}}
            <div class="relative hidden sm:block">
                <button
                    type="button"
                    class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-white/80 hover:text-white hover:bg-white/10 border border-white/20 transition-colors"
                    @click="favOpen = ! favOpen; langOpen = false"
                    :aria-expanded="favOpen"
                    aria-haspopup="menu"
                    title="Favoritas"
                >
                    <span class="text-amber-400" aria-hidden="true">★</span>
                    <span class="sr-only">Favoritas</span>
                    <span class="text-[10px]" aria-hidden="true">▾</span>
                </button>

                <div
                    x-show="favOpen"
                    x-transition
                    @click.outside="favOpen = false"
                    role="menu"
                    class="absolute right-0 mt-2 w-56 rounded-lg bg-ui-card text-text-primary shadow-dropdown border border-ui-border-l overflow-hidden z-[300]"
                >
                    <template x-if="favorites.length === 0 && !favError">
                        <div class="px-3 py-2 text-xs text-text-muted">No tienes favoritos.</div>
                    </template>
                    <template x-if="favError">
                        <div class="px-3 py-2 text-xs text-text-muted">No se pudieron cargar.</div>
                    </template>
                    <template x-for="fav in favorites" :key="fav.id">
                        <a
                            :href="fav.traefik_url || '#'"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-ui-body transition-colors"
                        >
                            <span class="text-amber-500 text-xs shrink-0" aria-hidden="true">★</span>
                            <span class="truncate" x-text="fav.name"></span>
                        </a>
                    </template>
                </div>
            </div>

            {{-- Selector de idioma (desktop) --}}
            <div class="relative hidden sm:block">
                <button
                    type="button"
                    class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-white/80 hover:text-white hover:bg-white/10 border border-white/20 transition-colors"
                    @click="langOpen = ! langOpen"
                >
                    <span class="sr-only">{{ __('app.nav_language') }}</span>
                    {{ strtoupper(app()->getLocale()) }}
                    <span class="text-[10px]" aria-hidden="true">▾</span>
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
                class="hidden sm:inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                :class="isDark ? 'text-amber-400 hover:bg-amber-400/10' : 'text-white/80 hover:bg-white/10'"
                @click="toggleDarkMode()"
                :aria-pressed="isDark"
                :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
            >
                <span class="sr-only" x-text="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"></span>
                {{-- Sun icon (visible when isDark) --}}
                <svg x-show="isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                </svg>
                {{-- Moon icon (visible when light) --}}
                <svg x-show="!isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                </svg>
            </button>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                @csrf
                <button
                    type="submit"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-colors"
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
