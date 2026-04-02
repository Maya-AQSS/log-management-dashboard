<x-layout>
    <div class="w-full mt-6">
        <h1 class="text-3xl md:text-3xl font-bold text-text-primary dark:text-text-dark-primary">{{ __('dashboard.title') }}</h1>
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
