<aside class="fixed inset-y-0 left-0 w-64 bg-ui-sidebar dark:bg-ui-dark-bg flex flex-col z-[100] border-r border-white/10 dark:border-ui-dark-border">
    {{-- Logo --}}
    <div class="h-14 flex items-center px-5 border-b border-white/10 dark:border-ui-dark-border-l">
        <span class="text-lg font-bold text-white tracking-wide">
            {{ __('app.app_name') }}
        </span>
    </div>

    {{-- Navegación --}}
    <nav class="flex-1 py-3 px-2 space-y-0.5 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="{{ $linkClasses('dashboard*') }}" data-nav="dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            {{ __('dashboard.menu') }}
        </a>

        <a href="{{ route('logs.index') }}" class="{{ $linkClasses('logs*') }}" data-nav="logs">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
            </svg>
            {{ __('logs.menu') }}
        </a>

        <a href="{{ route('archived-logs.index') }}" class="{{ $linkClasses('archived-logs*') }}" data-nav="archived-logs">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0">
                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
            </svg>
            {{ __('archived_logs.menu') }}
        </a>

        <a href="{{ route('error-codes.index') }}" class="{{ $linkClasses('error-codes*') }}" data-nav="error-codes">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ __('error_codes.menu') }}
        </a>
    </nav>

    {{-- Footer --}}
    <div class="border-t border-white/10 px-4 py-3 shrink-0">
        <p class="text-xs text-white/40">{{ __('app.app_name') }} v1.0</p>
    </div>
</aside>
