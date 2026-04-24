<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\BrowserTestSeeder;
use Tests\DuskTestCase;

class LogsTableTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Logs table renders with seeded data.
     */
    public function test_logs_table_renders(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->assertSee('Something went wrong in TestApp');
        });
    }

    /**
     * Live search (wire:model.live.debounce) filters table rows.
     */
    public function test_search_filters_logs(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                // Both logs visible initially
                ->assertSee('Something went wrong in TestApp')
                ->assertSee('A low-severity issue occurred in TestApp')
                // Type a unique substring — only one log matches
                ->type('input[wire\\:model\\.live\\.debounce\\.400ms]', 'Something went wrong')
                ->pause(600) // wait for debounce + Livewire update
                ->assertSee('Something went wrong in TestApp')
                ->assertDontSee('A low-severity issue occurred in TestApp');
        });
    }

    /**
     * Reset filters button clears search and shows all logs again.
     */
    public function test_reset_filters_restores_all_logs(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->type('input[wire\\:model\\.live\\.debounce\\.400ms]', 'Something went wrong')
                ->pause(600)
                ->assertDontSee('A low-severity issue occurred in TestApp')
                ->click('button[wire\\:click="resetFilters"]')
                ->pause(400)
                ->assertSee('Something went wrong in TestApp')
                ->assertSee('A low-severity issue occurred in TestApp');
        });
    }

    /**
     * Severity checkbox filter: selecting 'critical' hides low-severity logs.
     */
    public function test_severity_filter_shows_only_selected_severity(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                // Open the severity <details> element
                ->click('details summary')
                ->pause(200)
                // Check the 'critical' checkbox
                ->check('input[type="checkbox"][value="critical"]')
                // Click Apply
                ->click('button[x-on\\:click="$dispatch(\'logs-apply-requested\')"]')
                ->pause(600)
                ->assertSee('Something went wrong in TestApp')
                ->assertDontSee('A low-severity issue occurred in TestApp');
        });
    }

    /**
     * Sorting by severity column toggles sort direction indicator.
     */
    public function test_sort_by_severity_column(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->click('button[wire\\:click="sortByColumn(\'severity\')"]')
                // Wait up to 3s for the sort arrow to appear anywhere on the page
                ->waitForText('↑', 3000);
        });
    }

    /**
     * Clicking a table row navigates to the log detail page.
     */
    public function test_clicking_row_navigates_to_log_detail(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->click("tr[data-href*='/logs/{$seed->logId}']")
                ->waitForLocation("/logs/{$seed->logId}")
                ->assertPathIs("/logs/{$seed->logId}");
        });
    }
}
