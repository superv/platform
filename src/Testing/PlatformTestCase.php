<?php

namespace SuperV\Platform\Testing;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\Module\Jobs\ActivatePort;
use SuperV\Platform\Domains\Droplet\Port\Port;
use Tests\CreatesApplication;
use Tests\TestCase;

class PlatformTestCase extends TestCase
{
    use RefreshDatabase;
    use CreatesApplication;
    use DispatchesJobs;

    protected $installs = [];

    protected $port;

    protected $theme;

    protected $platformInstalled = false;

    protected $afterPlatformInstalledCallbacks = [];

    protected function setUp()
    {
        parent::setUp();

        $this->setUpMacros();

        $this->artisan('install');

        foreach ($this->installs as $droplet) {
            $this->artisan('droplet:install', ['droplet' => $droplet]);
        }

        foreach ($this->afterPlatformInstalledCallbacks as $callback) {
            call_user_func($callback);
        }

        $manager = app(DropletManager::class);
        $manager->load();

        $this->setUpPort();

        $manager->boot();

        $this->setUpTheme();

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    protected function setUpMacros()
    {
        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });
    }

    protected function setUpPort()
    {
        if (! $this->port) {
            return null;
        }

        /** @var Port $port */
        $port = superv('droplets')->bySlug($this->port);
        $this->dispatch(new ActivatePort($port));

        $routes = superv('routes')->byPort($port->getSlug());
        $port->registerRoutes($routes);

        return $port;
    }

    protected function setUpTheme()
    {
        if (! $this->theme) {
            return;
        }
        $theme = superv('droplets')->bySlug($this->theme);
        if (!$theme) {
            throw new \Exception("Theme not found: {$this->theme}");
        }
        superv('assets')->addPath('theme', $theme->getPath('resources'));
    }

    public function afterPlatformInstalled(callable $callback)
    {
        $this->afterPlatformInstalledCallbacks[] = $callback;

        if ($this->platformInstalled) {
            call_user_func($callback);
        }
    }
}