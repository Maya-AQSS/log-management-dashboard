<x-layout>
    <div class="w-full flex flex-col gap-4 md:flex-row md:items-center mt-6">
        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-slate-100">{{ __('dashboard.title') }}</h1>

        <form method="GET" action="{{ route('dashboard') }}" class="md:ml-auto">
            <label class="inline-flex items-center gap-2 rounded-full border border-[#5b3853] dark:border-[#9c6b91] bg-white dark:bg-slate-900 px-4 py-2 text-sm font-medium text-[#5b3853] dark:text-[#f0deea] shadow-sm">
                <input
                    type="checkbox"
                    name="include_archived"
                    value="1"
                    @checked($includeArchived)
                    onchange="this.form.submit()"
                    class="h-4 w-4 rounded border-slate-300 text-[#5b3853] focus:ring-[#5b3853]/30"
                >
                <span>{{ __('dashboard.filters.include_archived') }}</span>
            </label>
        </form>
    </div>

    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3" style="column-gap: 2rem; row-gap: 2rem;">
        @foreach($cards as $card)
            <x-dashboard-card
                :title="$card['title']"
                :href="$card['href']"
                :severity-key="$card['key']"
                :unresolved-count="$card['unresolvedCount']"
                :resolved-count="$card['resolvedCount']"
                :unresolved-label="$unresolvedLabel"
                :resolved-label="$resolvedLabel"
            />
        @endforeach
    </div>
</x-layout>
