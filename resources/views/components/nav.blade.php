<header x-data="{ open: false }" class="bg-gradient-to-r from-[#714b67] to-[#5b3853] text-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        {{-- Logo / título izquierda --}}
        <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center text-xs font-semibold">
                LM
            </div>
            <span class="text-sm font-semibold tracking-wide uppercase">
                Log Management Dashboard
            </span>
        </div>

        {{-- Menú centrado (oculto en mobile) --}}
        <nav class="hidden md:flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="{{ $linkClasses('dashboard*') }}">Dashboard</a>
            <a href="{{ route('logs.index') }}" class="{{ $linkClasses('logs*') }}">Logs</a>
            <a href="{{ route('archived-logs.index') }}" class="{{ $linkClasses('archived-logs*') }}">Histórico</a>
            <a href="{{ route('error-codes.index') }}" class="{{ $linkClasses('error-codes*') }}">Error Codes</a>
        </nav>

        {{-- Zona derecha: usuario + logout + hamburguesa --}}
        <div class="flex items-center gap-3">
            <span class="hidden sm:inline text-sm text-white/80">
                {{ auth()->user()->name ?? 'Usuario' }}
            </span>

            <form method="POST" action="#" class="hidden sm:block">
                @csrf
                <button
                    type="submit"
                    class="px-3 py-1.5 rounded-full bg-[#f7a736] hover:bg-[#e28f1f] text-xs font-semibold text-[#1e1a24] shadow-sm"
                >
                    Cerrar sesión
                </button>
            </form>

            {{-- Menú móvil --}}
            <button
                type="button"
                class="md:hidden inline-flex items-center justify-center p-2 rounded-full hover:bg-white/10"
                @click="open = ! open"
            >
                <span class="sr-only">Abrir menú</span>
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
            <a href="{{ route('dashboard') }}" class="block {{ $linkClasses('dashboard*') }}">Dashboard</a>
            <a href="{{ route('logs.index') }}" class="block {{ $linkClasses('logs*') }}">Logs</a>
            <a href="{{ route('archived-logs.index') }}" class="block {{ $linkClasses('archived-logs*') }}">Histórico</a>
            <a href="{{ route('error-codes.index') }}" class="block {{ $linkClasses('error-codes*') }}">Error Codes</a>
        </div>
    </nav>
</header>