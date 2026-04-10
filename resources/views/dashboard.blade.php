<x-layout>
    <div class="py-3 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
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

    @if($applicationTotals !== [])
        <section class="py-3 w-full" aria-labelledby="dashboard-by-app-heading">
            <div
                class="rounded-lg border border-ui-border bg-ui-card p-6 shadow-card dark:border-ui-dark-border dark:bg-ui-dark-card"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div class="min-w-0 border-l-4 border-odoo-purple pl-4">
                        <h2
                            id="dashboard-by-app-heading"
                            class="text-xl font-bold tracking-tight text-text-primary dark:text-text-dark-primary sm:text-2xl"
                        >
                            {{ __('dashboard.by_application') }}
                        </h2>
                        <p class="mt-1.5 max-w-xl text-sm leading-relaxed text-text-secondary dark:text-text-dark-secondary">
                            {{ __('dashboard.by_application_hint') }}
                        </p>
                    </div>
                </div>

                <ul class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2" role="list">
                    @foreach($applicationTotals as $row)
                        <li>
                            <x-dashboard-application-link
                                :href="$row['href']"
                                :name="$row['name']"
                                :total="$row['total']"
                            />
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layout>
