<x-layout>
    <div class="w-full mt-6">
        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-slate-100">{{ __('dashboard.title') }}</h1>
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
