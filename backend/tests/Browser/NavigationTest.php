<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\BrowserTestSeeder;
use Tests\DuskTestCase;

class NavigationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Sidebar renders the logs navigation link.
     */
    public function test_sidebar_renders_navigation_links(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->assertPresent('[data-nav="logs"]')
                ->assertPresent('[data-nav="archived-logs"]')
                ->assertPresent('[data-nav="error-codes"]');
        });
    }

    /**
     * The active route link receives the active CSS class from Sidebar::linkClasses().
     */
    public function test_active_route_is_highlighted(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->assertAttributeContains('[data-nav="logs"]', 'class', 'bg-ui-sidebar-active');
        });
    }

    /**
     * Topbar shows the page title element.
     */
    public function test_topbar_shows_page_title(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->assertPresent('[data-topbar-title]');
        });
    }

    /**
     * Clicking archived-logs in the sidebar navigates there and updates topbar.
     */
    public function test_sidebar_navigation_updates_topbar(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->click('[data-nav="archived-logs"]')
                ->waitForLocation('/archived-logs')
                ->assertPresent('[data-topbar-title]')
                ->assertAttributeContains('[data-nav="archived-logs"]', 'class', 'bg-ui-sidebar-active');
        });
    }

    /**
     * User dropdown opens on click and shows the logout button.
     */
    public function test_user_dropdown_opens(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->click('[data-user-menu-trigger]')
                ->waitFor('[data-user-menu-dropdown]')
                ->assertVisible('[data-user-menu-dropdown]');
        });
    }
}
