<x-layout>
    <div class="w-full pb-2">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50 md:text-4xl">
            {{ __('dashboard.title') }}
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
            {{ __('dashboard.subtitle') }}
        </p>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-x-10 lg:gap-y-8">
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
        <section class="mt-20 w-full md:mt-28" aria-labelledby="dashboard-by-app-heading">
            <div
                class="rounded-2xl border border-slate-200/90 bg-gradient-to-br from-slate-50/90 via-white to-[#faf8fc] p-6 shadow-inner shadow-slate-900/[0.03] dark:border-slate-700 dark:from-slate-900/80 dark:via-slate-950 dark:to-slate-900/60"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div class="min-w-0 border-l-4 border-odoo-purple pl-4">
                        <h2
                            id="dashboard-by-app-heading"
                            class="text-xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-2xl"
                        >
                            {{ __('dashboard.by_application') }}
                        </h2>
                        <p class="mt-1.5 max-w-xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
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
