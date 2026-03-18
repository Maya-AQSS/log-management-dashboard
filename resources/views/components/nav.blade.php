<header x-data="{ open: false, langOpen: false }" class="bg-gradient-to-r from-[#714b67] to-[#5b3853] text-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        {{-- Logo / título izquierda --}}
        <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center text-xs font-semibold">
                LM
            </div>
            <span class="text-sm font-semibold tracking-wide uppercase">
                {{ __('app.app_name') }}
            </span>
        </div>

        {{-- Menú centrado (oculto en mobile) --}}
        <nav class="hidden md:flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="{{ $linkClasses('dashboard*') }}">{{ __('dashboard.menu') }}</a>
            <a href="{{ route('logs.index') }}" class="{{ $linkClasses('logs*') }}">{{ __('logs.menu') }}</a>
            <a href="{{ route('archived-logs.index') }}" class="{{ $linkClasses('archived-logs*') }}">{{ __('archived_logs.menu') }}</a>
            <a href="{{ route('error-codes.index') }}" class="{{ $linkClasses('error-codes*') }}">{{ __('error_codes.menu') }}</a>
        </nav>

        {{-- Zona derecha: usuario + logout + hamburguesa --}}
        <div class="flex items-center gap-3">
            <span class="hidden sm:inline text-sm text-white/80">
                {{ auth()->user()->name ?? __('app.nav_user') }}
            </span>

            {{-- Selector de idioma (desktop) --}}
            <div class="relative hidden sm:block">
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/15 text-xs font-semibold"
                    @click="langOpen = ! langOpen"
                >
                    <span class="sr-only">{{ __('app.nav_language') }}</span>
                    {{ strtoupper(app()->getLocale()) }}
                </button>

                <div
                    x-show="langOpen"
                    x-transition
                    @click.outside="langOpen = false"
                    class="absolute right-0 mt-2 w-40 rounded-xl bg-white text-slate-900 shadow-lg border border-slate-200 overflow-hidden"
                >
                    @foreach (config('app.supported_locales', ['es', 'en', 'va']) as $locale)
                        <form method="POST" action="{{ route('lang.switch', $locale) }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 {{ app()->getLocale() === $locale ? 'font-semibold' : '' }}"
                            >
                                {{ __('app.locale_'.$locale) }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                @csrf
                <button
                    type="submit"
                    class="px-3 py-1.5 rounded-full bg-[#f7a736] hover:bg-[#e28f1f] text-xs font-semibold text-[#1e1a24] shadow-sm"
                >
                    {{ __('app.nav_logout') }}
                </button>
            </form>

            {{-- Menú móvil --}}
            <button
                type="button"
                class="md:hidden inline-flex items-center justify-center p-2 rounded-full hover:bg-white/10"
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
        class="md:hidden border-t border-white/10 bg-gradient-to-r from-[#714b67] to-[#5b3853] text-white shadow-sm"
    >
        <div class="px-4 py-3 space-y-2">
            <a href="{{ route('dashboard') }}" class="block {{ $linkClasses('dashboard*') }}">{{ __('dashboard.menu') }}</a>
            <a href="{{ route('logs.index') }}" class="block {{ $linkClasses('logs*') }}">{{ __('logs.menu') }}</a>
            <a href="{{ route('archived-logs.index') }}" class="block {{ $linkClasses('archived-logs*') }}">{{ __('archived_logs.menu') }}</a>
            <a href="{{ route('error-codes.index') }}" class="block {{ $linkClasses('error-codes*') }}">{{ __('error_codes.menu') }}</a>

            {{-- Selector de idioma (mobile) --}}
            <div class="pt-2 border-t border-white/10">
                <div class="text-xs uppercase tracking-wide text-white/70 mb-2">
                    {{ __('app.nav_language') }}
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach (config('app.supported_locales', ['es', 'en', 'va']) as $locale)
                        <form method="POST" action="{{ route('lang.switch', $locale) }}">
                            @csrf
                            <button
                                type="submit"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold {{ app()->getLocale() === $locale ? 'bg-white/20' : 'bg-white/10 hover:bg-white/15' }}"
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
