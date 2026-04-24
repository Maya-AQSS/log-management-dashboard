<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     *
     * ChromeDriver runs on the host machine (where Chromium is installed).
     * Start it before running tests: chromedriver --port=9515
     * Then run: docker exec maya_log_mgmt php artisan dusk
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        // ChromeDriver is managed externally on the host — no auto-start needed.
        // See scripts/dusk.sh for the full setup.
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * DUSK_DRIVER_URL: URL to ChromeDriver on host (172.18.0.1:9515 from inside the container)
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
                '--no-sandbox',
                '--disable-dev-shm-usage',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://172.18.0.1:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
