<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\BrowserTestSeeder;
use Tests\DuskTestCase;

class ArchivedLogsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Archived logs index renders the seeded archived log.
     */
    public function test_archived_logs_index_renders(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/archived-logs')
                ->assertSee('Archived critical error');
        });
    }

    /**
     * Clicking a row navigates to the archived log detail.
     */
    public function test_clicking_row_navigates_to_detail(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/archived-logs')
                ->click("tr[data-href*='/archived-logs/{$seed->archivedLogId}']")
                ->waitForLocation("/archived-logs/{$seed->archivedLogId}")
                ->assertPathIs("/archived-logs/{$seed->archivedLogId}");
        });
    }

    /**
     * Archived log detail page renders the log message.
     */
    public function test_archived_log_detail_renders(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/archived-logs/{$seed->archivedLogId}")
                ->assertSee('Archived critical error');
        });
    }

    /**
     * Delete button on archived log detail opens confirm modal.
     */
    public function test_delete_button_opens_confirm_modal(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/archived-logs/{$seed->archivedLogId}")
                ->click('button[x-on\\:click="confirmDeleteOpen = true"]')
                ->waitFor('[data-confirm-modal="delete"]')
                ->assertVisible('[data-confirm-modal="delete"]');
        });
    }

    /**
     * Confirming delete soft-deletes and redirects to index.
     */
    public function test_confirm_delete_redirects_to_index(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/archived-logs/{$seed->archivedLogId}")
                ->click('button[x-on\\:click="confirmDeleteOpen = true"]')
                ->waitFor('[data-confirm-modal="delete"]')
                ->click('[data-confirm-modal="delete"] form button[type="submit"]')
                ->waitForLocation('/archived-logs')
                ->assertPathIs('/archived-logs');
        });
    }
}
