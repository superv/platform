<?php

namespace SuperV\Platform\Testing;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use SuperV\Platform\Domains\Droplet\Module\Jobs\ActivatePort;
use SuperV\Platform\Facades\Inflator;
use SuperV\Platform\PlatformServiceProvider;
use SuperV\Platform\Traits\RegistersRoutes;
use Tests\CreatesApplication;
use Tests\TestCase;

class PlatformTestCase extends TestCase
{
    use RefreshDatabase;
    use CreatesApplication;
    use RegistersRoutes;
    use DispatchesJobs;

    protected $port;

    protected $platformInstalled = false;

    protected $afterPlatformInstalledCallbacks = [];

    protected function setUp()
    {
        parent::setUp();

        $this->setUpMacros();

        $this->artisan('install');
        $port = $this->setUpPort();

        foreach ($this->afterPlatformInstalledCallbacks as $callback) {
            call_user_func($callback);
        }

        if ($port) {
            $this->dispatch(new ActivatePort($port));
        }
        $this->app->register(PlatformServiceProvider::class, [], true);
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

        $port = superv('droplets')->bySlug($this->port)->newDropletInstance();

        if ($config = config(sprintf('superv.ports.%s', strtolower($port->getName())))) {
            Inflator::inflate($port, $config);
        }
        superv('ports')->push($port);

        return $port;
    }

    protected function setUpTheme($themeSlug)
    {
        $theme = superv('droplets')->bySlug($themeSlug);
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