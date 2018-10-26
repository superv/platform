<?php

namespace SuperV\Platform\Testing;

use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Droplet\Locator;
use SuperV\Platform\PlatformServiceProvider;
use Tests\CreatesApplication;
use Tests\TestCase;

class PlatformTestCase extends TestCase
{
    use RefreshDatabase;
    use CreatesApplication;
    use DispatchesJobs;
    use TestHelpers;

    protected $installs = [];

    protected $port;

    protected $theme;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('superv:install');
        config(['superv.installed' => true]);

        $this->handlePostInstallCallbacks();

        foreach ($this->installs as $droplet) {
            app(Installer::class)->setLocator(new Locator())
                                 ->setSlug($droplet)
                                 ->install();
        }

        (new PlatformServiceProvider($this->app))->boot();

        $this->setUpMacros();
    }

    /**
     * Load model factories from path.
     *
     * @param  string $path
     * @return $this
     */
    protected function withFactories(string $path)
    {
        return $this->loadFactoriesUsing($this->app, $path);
    }

    /**
     * Load model factories from path using Application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @param  string                                       $path
     * @return $this
     */
    protected function loadFactoriesUsing($app, string $path)
    {
        $app->make(ModelFactory::class)->load($path);

        return $this;
    }
}