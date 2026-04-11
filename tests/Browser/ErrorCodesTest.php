<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Support\BrowserTestSeeder;
use Tests\DuskTestCase;

class ErrorCodesTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Error codes index lists seeded error code.
     */
    public function test_error_codes_index_renders(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/error-codes')
                ->assertSee('ERR-001');
        });
    }

    /**
     * Create button navigates to the create form.
     */
    public function test_create_button_navigates_to_form(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/error-codes')
                ->clickLink(__('error_codes.buttons.create'))
                ->waitForLocation('/error-codes/create')
                ->assertPathIs('/error-codes/create')
                ->assertPresent('form#error-code-main-form');
        });
    }

    /**
     * Submitting the create form with valid data saves and redirects to detail.
     */
    public function test_create_form_saves_new_error_code(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit('/error-codes/create')
                ->type('#name', 'New Error')
                ->type('#code', 'NEW-001')
                ->select('#application_id', (string) $seed->applicationId)
                ->press('button[type="submit"][form="error-code-main-form"]')
                ->waitUntil("window.location.pathname.match(/^\\/error-codes\\/\\d+$/)")
                ->assertPathContains('/error-codes/');
        });
    }

    /**
     * Error code detail page shows the code and an Edit button.
     */
    public function test_error_code_detail_shows_edit_button(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/error-codes/{$seed->errorCodeId}")
                ->assertSee('ERR-001')
                ->assertPresent('button[wire\\:click="enableEdit"]');
        });
    }

    /**
     * Clicking Edit enables the form fields.
     */
    public function test_edit_button_enables_form(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/error-codes/{$seed->errorCodeId}")
                ->click('button[wire\\:click="enableEdit"]')
                ->pause(400)
                ->assertEnabled('#name')
                ->assertEnabled('#code');
        });
    }

    /**
     * Delete button opens the confirm modal.
     */
    public function test_delete_button_opens_confirm_modal(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/error-codes/{$seed->errorCodeId}")
                ->click('button[x-on\\:click="confirmDeleteOpen = true"]')
                ->waitFor('[data-confirm-modal="delete"]')
                ->assertVisible('[data-confirm-modal="delete"]');
        });
    }

    /**
     * Confirming delete removes the error code and redirects to index.
     */
    public function test_confirm_delete_removes_error_code(): void
    {
        $seed = BrowserTestSeeder::seedAll();

        $this->browse(function (Browser $browser) use ($seed) {
            $browser->loginAs($seed->user)
                ->visit("/error-codes/{$seed->errorCodeId}")
                ->click('button[x-on\\:click="confirmDeleteOpen = true"]')
                ->waitFor('[data-confirm-modal="delete"]')
                ->click('[data-confirm-modal="delete"] form button[type="submit"]')
                ->waitForLocation('/error-codes')
                ->assertPathIs('/error-codes')
                ->assertDontSee('ERR-001');
        });
    }
}
