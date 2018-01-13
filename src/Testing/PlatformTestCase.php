<?php

namespace SuperV\Platform\Testing;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\TestResponse;
use SuperV\Platform\Domains\Droplet\Module\Jobs\ActivatePort;
use SuperV\Platform\Domains\Droplet\Port\Port;
use SuperV\Platform\Facades\Inflator;
use SuperV\Platform\PlatformServiceProvider;
use SuperV\Platform\Traits\RegistersRoutes;
use Tests\CreatesApplication;
use Tests\TestCase;

class PlatformTestCase extends TestCase
{
    use CreatesApplication;
    use RegistersRoutes;
    use DispatchesJobs;

    protected $port;

    protected function setUp()
    {
        parent::setUp();

        $this->setUpMacros();

        $this->artisan('install');

        $this->setUpPort();

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
            return;
        }

        $port = superv('droplets')->bySlug($this->port)->newDropletInstance();

        if ($config = config($this->port)) {
            Inflator::inflate($port, $config);
        }
        superv('ports')->push($port);
        $this->dispatch(new ActivatePort($port));
    }

    protected function setUpTheme($themeSlug)
    {
        $theme = superv('droplets')->bySlug($themeSlug);
        superv('assets')->addPath('theme', $theme->getPath('resources'));
    }
}