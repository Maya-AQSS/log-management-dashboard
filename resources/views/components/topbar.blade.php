<header
    x-data="{
        userMenuOpen: false,
        favOpen: false,
        favorites: [],
        favError: false,
        notifOpen: false,
        notifications: [],
        unreadCount: 0,
        isDark: document.documentElement.classList.contains('dark'),
        toggleDarkMode() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
        changeLocale(locale) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/lang/' + locale;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name=csrf-token]')?.content || '';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        },
        getCookie(name) {
            return document.cookie.split('; ').find(r => r.startsWith(name + '='))?.split('=')[1];
        },
        parseJwtSub(token) {
            try {
                const payload = token.split('.')[1];
                return JSON.parse(atob(payload.replace(/-/g,'+').replace(/_/g,'/'))).sub;
            } catch { return null; }
        },
        async loadFavorites() {
            this.favError = false;
            const token = this.getCookie('session_token');
            if (!token) { this.favorites = []; this.favError = true; return; }
            const sub = this.parseJwtSub(token);
            if (!sub) { this.favorites = []; this.favError = true; return; }
            try {
                const r = await fetch(`http://maya_dashboard_api.localhost/api/v1/dashboard/user/${encodeURIComponent(sub)}/favorites`, {
                    headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
                });
                if (!r.ok) { this.favError = true; this.favorites = []; return; }
                const p = await r.json();
                this.favorites = Array.isArray(p) ? p : (p?.data ?? []);
            } catch { this.favError = true; this.favorites = []; }
        },
        submitLogout() {
            const form = document.getElementById('topbar-logout-form');
            if (form) form.submit();
        },
        async loadNotifications() {
            const token = this.getCookie('session_token');
            if (!token) { this.notifications = []; this.unreadCount = 0; return; }
            const base = 'http://maya_dashboard_api.localhost';
            try {
                const [listResp, countResp] = await Promise.all([
                    fetch(`${base}/api/v1/notifications?per_page=10`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
                    fetch(`${base}/api/v1/notifications/unread-count`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
                ]);
                if (listResp.ok) {
                    const d = await listResp.json();
                    this.notifications = Array.isArray(d?.data) ? d.data : [];
                }
                if (countResp.ok) {
                    const d = await countResp.json();
                    this.unreadCount = Number(d?.unread ?? 0);
                }
            } catch {}
        },
        async markNotifRead(id) {
            const token = this.getCookie('session_token');
            if (!token) return;
            try {
                const r = await fetch(`http://maya_dashboard_api.localhost/api/v1/notifications/${id}/read`, {
                    method: 'POST', headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
                });
                if (r.ok) {
                    const n = this.notifications.find(x => x.id === id);
                    if (n && !n.read_at) { n.read_at = new Date().toISOString(); this.unreadCount = Math.max(0, this.unreadCount - 1); }
                }
            } catch {}
        },
        async markAllNotifRead() {
            const token = this.getCookie('session_token');
            if (!token) return;
            try {
                const r = await fetch('http://maya_dashboard_api.localhost/api/v1/notifications/mark-all-read', {
                    method: 'POST', headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
                });
                if (r.ok) {
                    const now = new Date().toISOString();
                    this.notifications = this.notifications.map(n => n.read_at ? n : { ...n, read_at: now });
                    this.unreadCount = 0;
                }
            } catch {}
        },
        formatRel(iso) {
            const diff = (Date.now() - new Date(iso).getTime()) / 1000;
            if (diff < 60) return 'ahora';
            if (diff < 3600) return Math.floor(diff / 60) + ' min';
            if (diff < 86400) return Math.floor(diff / 3600) + ' h';
            return new Date(iso).toLocaleDateString();
        },
        init() {
            this.loadFavorites();
            this.loadNotifications();
            setInterval(() => this.loadNotifications(), 60000);
            window.addEventListener('storage', (e) => { if (e.key === 'maya:favorites-updated-at') this.loadFavorites(); });
        },
    }"
    class="h-14 bg-ui-topbar dark:bg-ui-dark-topbar shadow-topbar flex items-center justify-between px-6 z-[200] shrink-0 border-b border-ui-border dark:border-ui-dark-border"
>
    {{-- Título de página --}}
    <h1 class="text-md font-semibold text-text-primary dark:text-text-dark-primary truncate" data-topbar-title>
        {{ $pageTitle }}
    </h1>

    {{-- Controles derecha --}}
    <div class="flex items-center gap-2">
        @php
            $userName = auth()->user()->name ?? __('app.nav_user');
            $initial  = strtoupper(substr($userName, 0, 1));
        @endphp

        {{-- Campana de notificaciones --}}
        <div class="relative">
            <button
                type="button"
                class="relative flex items-center justify-center w-8 h-8 rounded-lg text-text-secondary dark:text-text-dark-secondary hover:bg-ui-body dark:hover:bg-ui-dark-card transition-colors"
                @click="notifOpen = ! notifOpen; favOpen = false; userMenuOpen = false"
                :aria-expanded="notifOpen"
                aria-haspopup="menu"
                title="Notificaciones"
                aria-label="Notificaciones"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5" aria-hidden="true">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                </svg>
                <span
                    x-show="unreadCount > 0"
                    x-text="unreadCount > 9 ? '9+' : unreadCount"
                    class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"
                    style="display: none;"
                ></span>
            </button>
            <div
                x-show="notifOpen"
                x-transition
                @click.outside="notifOpen = false"
                role="menu"
                class="absolute right-0 top-full mt-1 w-80 max-w-[92vw] bg-ui-card dark:bg-ui-dark-card border border-ui-border dark:border-ui-dark-border rounded-md shadow-lg z-[210]"
                style="display: none;"
            >
                <div class="px-3 py-2 flex items-center justify-between border-b border-ui-border dark:border-ui-dark-border">
                    <span class="text-sm font-semibold text-text-primary dark:text-text-dark-primary">Notificaciones</span>
                    <button x-show="unreadCount > 0" type="button" @click="markAllNotifRead()" class="text-xs text-odoo-purple hover:underline">Marcar todo como leído</button>
                </div>
                <div class="max-h-80 overflow-y-auto">
                    <template x-if="notifications.length === 0">
                        <div class="px-3 py-4 text-sm text-text-muted dark:text-text-dark-muted text-center">Sin notificaciones</div>
                    </template>
                    <template x-for="n in notifications" :key="n.id">
                        <button
                            type="button"
                            @click="if (!n.read_at) markNotifRead(n.id)"
                            class="w-full text-left px-3 py-2 border-b border-ui-border dark:border-ui-dark-border transition-colors hover:bg-ui-body dark:hover:bg-ui-dark-bg last:border-b-0 flex gap-2 items-start"
                            :class="n.read_at ? 'opacity-70' : ''"
                        >
                            <span class="mt-1.5 w-2 h-2 rounded-full shrink-0" :class="n.read_at ? 'bg-transparent' : 'bg-red-500'" aria-hidden="true"></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-medium text-text-primary dark:text-text-dark-primary truncate" x-text="n.title"></span>
                                    <span class="text-[10px] text-text-muted dark:text-text-dark-muted shrink-0" x-text="formatRel(n.created_at)"></span>
                                </div>
                                <p class="text-xs text-text-muted dark:text-text-dark-muted mt-0.5 line-clamp-2" x-text="n.body"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Favoritos --}}
        <div class="relative">
            <button
                type="button"
                class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-text-secondary dark:text-text-dark-secondary hover:bg-ui-body dark:hover:bg-ui-dark-card border border-ui-border dark:border-ui-dark-border transition-colors"
                @click="favOpen = ! favOpen; userMenuOpen = false"
                :aria-expanded="favOpen"
                aria-haspopup="menu"
                title="Favoritas"
            >
                <span class="text-amber-500" aria-hidden="true">★</span>
                <span class="sr-only">Favoritas</span>
                <span class="text-[10px]" aria-hidden="true">▾</span>
            </button>
            <div
                x-show="favOpen"
                x-transition
                @click.outside="favOpen = false"
                role="menu"
                class="absolute right-0 mt-2 w-56 rounded-lg bg-ui-card dark:bg-ui-dark-card text-text-primary dark:text-text-dark-primary shadow-lg border border-ui-border dark:border-ui-dark-border overflow-hidden z-[300]"
                style="display: none;"
            >
                <template x-if="favorites.length === 0 && !favError">
                    <div class="px-3 py-2 text-xs text-text-muted dark:text-text-dark-muted">Sin favoritos</div>
                </template>
                <template x-if="favError">
                    <div class="px-3 py-2 text-xs text-text-muted dark:text-text-dark-muted">No se pudieron cargar.</div>
                </template>
                <template x-for="fav in favorites" :key="fav.id">
                    <a
                        :href="fav.traefik_url || '#'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-ui-body dark:hover:bg-ui-dark-bg transition-colors"
                    >
                        <span class="text-amber-500 text-xs shrink-0" aria-hidden="true">★</span>
                        <span class="truncate" x-text="fav.name"></span>
                    </a>
                </template>
            </div>
        </div>

        {{-- Selector de idioma --}}
        <select
            class="text-xs border border-ui-border dark:border-ui-dark-border bg-transparent text-text-secondary dark:text-text-dark-secondary rounded px-1.5 py-0.5 outline-none focus:border-odoo-purple cursor-pointer"
            @change="changeLocale($event.target.value)"
            aria-label="{{ __('app.nav_language') }}"
        >
            @foreach (config('app.supported_locales', ['es', 'en', 'va']) as $locale)
                <option value="{{ $locale }}" {{ app()->getLocale() === $locale ? 'selected' : '' }}>
                    {{ __('app.locale_'.$locale) }}
                </option>
            @endforeach
        </select>

        {{-- Toggle dark mode --}}
        <button
            type="button"
            class="rounded-lg p-2 transition-colors"
            :class="isDark ? 'text-amber-400 hover:bg-amber-400/10' : 'text-slate-600 hover:bg-ui-body dark:text-text-dark-secondary dark:hover:bg-ui-dark-card'"
            @click="toggleDarkMode()"
            :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
            :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        >
            <svg x-show="isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true" style="display: none;">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
            </svg>
            <svg x-show="!isDark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
        </button>

        {{-- Menú de usuario con dropdown (Cerrar sesión) --}}
        <div class="relative">
            <button
                type="button"
                class="flex items-center gap-2 rounded-lg px-2 py-1 text-text-primary dark:text-text-dark-primary hover:bg-ui-body dark:hover:bg-ui-dark-card transition-colors"
                @click="userMenuOpen = ! userMenuOpen; favOpen = false"
                :aria-expanded="userMenuOpen"
                aria-haspopup="menu"
            >
                <span class="font-medium hidden sm:inline text-sm">{{ $userName }}</span>
                <span class="w-8 h-8 rounded-full bg-odoo-purple flex items-center justify-center" title="{{ $userName }}">
                    <span class="text-xs font-bold text-white">{{ $initial }}</span>
                </span>
                <span
                    class="text-text-muted dark:text-text-dark-muted text-xs transition-transform"
                    :class="{ 'rotate-180': userMenuOpen }"
                    aria-hidden="true"
                >▾</span>
            </button>
            <div
                x-show="userMenuOpen"
                x-transition
                @click.outside="userMenuOpen = false"
                role="menu"
                class="absolute right-0 top-full mt-1 min-w-[180px] bg-ui-card dark:bg-ui-dark-card border border-ui-border dark:border-ui-dark-border rounded-md shadow-lg py-1 z-[210]"
                style="display: none;"
            >
                <button
                    type="button"
                    role="menuitem"
                    @click="userMenuOpen = false; submitLogout()"
                    class="w-full text-left px-4 py-2 text-sm text-text-primary dark:text-text-dark-primary hover:bg-ui-body dark:hover:bg-ui-dark-bg cursor-pointer bg-transparent border-0 transition-colors"
                >
                    {{ __('app.nav_logout') }}
                </button>
            </div>
        </div>

        {{-- Hidden logout form submitted by the dropdown --}}
        <form id="topbar-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
    </div>
</header>
