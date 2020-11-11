<?php

declare(strict_types=1);

namespace Dust;

use Closure;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;
use Laravel\Dusk\TestCase as DuskTestCase;
use Pest\TestSuite;

/**
 * @internal
 */
class TestCase extends DuskTestCase
{
    /**
     * Before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $directory = function (string $target): string {
            return implode(DIRECTORY_SEPARATOR, [
                TestSuite::getInstance()->rootPath,
                $target,
            ]);
        };

        Browser::$storeScreenshotsAt = $directory('screenshots');
        Browser::$storeConsoleLogAt  = $directory('console');
        Browser::$storeSourceAt      = $directory('source');
    }

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = new Application(dirname(__FILE__, 2));

        $app->singleton(
            HttpKernelContract::class,
            HttpKernel::class
        );

        $app->singleton(
            ConsoleKernelContract::class,
            ConsoleKernel::class
        );

        $app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        return $app;
    }

    /**
     * Determine the application's base URL.
     */
    protected function baseUrl(): string
    {
        return baseUrl();
    }

    /**
     * @param Closure|Page|string $route
     *
     * @return Browser|void
     */
    public function browse($route)
    {
        if ($route instanceof Closure) {
            return parent::browse($route);
        }

        /** @var \Laravel\Dusk\Browser $browser */
        $browser = null;

        $this->browse(function (Browser $browse) use (&$browser): void {
            $browser = $browse;
        });

        return $browser->visit($route);
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions())->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     *
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }
}
