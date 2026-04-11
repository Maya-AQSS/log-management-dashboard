<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\BrowserTestSeeder;
use Tests\DuskTestCase;

class DarkModeTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Clear localStorage before each test to prevent state leaking between methods.
     * All tests in this class share the same browser session, so localStorage persists.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clear localStorage before each test to prevent state contamination.
        // Must visit a real page first — localStorage is unavailable on data: URLs.
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')->script('localStorage.clear()');
        });
    }

    /**
     * Default mode is light (no 'dark' class on <html>).
     */
    public function test_default_mode_is_light(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->assertScript('!document.documentElement.classList.contains("dark")');
        });
    }

    /**
     * Clicking the dark-mode toggle adds 'dark' to <html> and persists to localStorage.
     */
    public function test_toggle_activates_dark_mode(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->click('[data-dark-mode-toggle]')
                ->pause(200)
                ->assertScript('document.documentElement.classList.contains("dark")')
                ->assertScript('localStorage.getItem("theme") === "dark"');
        });
    }

    /**
     * Dark mode preference persists when navigating to another page.
     */
    public function test_dark_mode_persists_across_navigation(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->click('[data-dark-mode-toggle]')
                ->pause(200)
                ->visit('/archived-logs')
                ->assertScript('document.documentElement.classList.contains("dark")');
        });
    }

    /**
     * Toggling again switches back to light mode.
     */
    public function test_toggle_back_to_light_mode(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/logs')
                ->click('[data-dark-mode-toggle]')
                ->pause(200)
                ->click('[data-dark-mode-toggle]')
                ->pause(200)
                ->assertScript('!document.documentElement.classList.contains("dark")')
                ->assertScript('localStorage.getItem("theme") === "light"');
        });
    }
}
