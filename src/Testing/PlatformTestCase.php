<?php

namespace SuperV\Platform\Testing;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\PlatformServiceProvider;
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

        $this->artisan('superv:install');
        config(['superv.installed' => true]);

        foreach ($this->installs as $droplet => $path) {
            app(Installer::class)->path($path)
                                 ->slug($droplet)
                                 ->install();
        }

        (new PlatformServiceProvider($this->app))->boot();

        $this->setUpMacros();
    }

    protected function setUpMacros()
    {
        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });

        EloquentCollection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value), "Failed asserting that the collection contains the specified value.");
        });

        EloquentCollection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value), "Failed asserting that the collection does not contain the specified value.");
        });

        EloquentCollection::macro('assertEquals', function ($items) {
            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
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
        if (! $theme) {
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