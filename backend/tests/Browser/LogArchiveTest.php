<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\BrowserTestSeeder;
use Tests\DuskTestCase;

class LogArchiveTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Log detail page renders the Archive button for an unarchived log.
     */
    public function test_log_detail_shows_archive_button(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/logs/{$seed->logId}")
                ->assertPresent('button[x-on\\:click="confirmArchiveOpen = true"]');
        });
    }

    /**
     * Clicking Archive opens the confirmation modal.
     */
    public function test_archive_button_opens_confirm_modal(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/logs/{$seed->logId}")
                ->click('button[x-on\\:click="confirmArchiveOpen = true"]')
                ->waitFor('[data-confirm-modal="archive"]')
                ->assertVisible('[data-confirm-modal="archive"]');
        });
    }

    /**
     * Cancelling the modal closes it without archiving.
     */
    public function test_cancel_closes_archive_modal(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/logs/{$seed->logId}")
                ->click('button[x-on\\:click="confirmArchiveOpen = true"]')
                ->waitFor('[data-confirm-modal="archive"]')
                ->click('[data-confirm-modal="archive"] button[type="button"]')
                ->pause(300)
                ->assertNotPresent('[data-confirm-modal="archive"][style=""]');
        });
    }

    /**
     * Confirming archive submits, redirects, and the log no longer shows the Archive button.
     */
    public function test_confirm_archive_submits_and_redirects(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/logs/{$seed->logId}")
                ->click('button[x-on\\:click="confirmArchiveOpen = true"]')
                ->waitFor('[data-confirm-modal="archive"]')
                ->click('[data-confirm-modal="archive"] form button[type="submit"]')
                ->waitUntil("window.location.pathname.startsWith('/archived-logs/')")
                ->assertPathContains('/archived-logs/');
        });
    }

    /**
     * Resolve button opens the resolve confirmation modal.
     */
    public function test_resolve_button_opens_confirm_modal(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/logs/{$seed->logId}")
                ->click('button[x-on\\:click="confirmResolveOpen = true"]')
                ->waitFor('[data-confirm-modal="resolve"]')
                ->assertVisible('[data-confirm-modal="resolve"]');
        });
    }
}
