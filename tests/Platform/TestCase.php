<?php

namespace Tests\Platform;

use Hub;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Droplet\Locator;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use SuperV\Platform\PlatformServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Temporary directory to be created and
     * afterwards deleted in storage folder
     *
     * @var string
     */
    protected $tmpDirectory;

    protected $packageProviders = [];

    protected $appConfig = [];

    protected $installs = [];

    protected function getPackageProviders($app)
    {
        return array_flatten(array_merge([PlatformServiceProvider::class], $this->packageProviders));
    }

    protected function getPackageAliases($app)
    {
//        return ['Platform' => PlatformFacade::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(realpath(__DIR__.'/../../'));

        if (! empty($this->appConfig)) {
            $app['config']->set($this->appConfig);
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../database/factories');

        if ($this->tmpDirectory) {
            $this->tmpDirectory = __DIR__.'/../tmp/'.$this->tmpDirectory;
            if (! file_exists($this->tmpDirectory)) {
                app('files')->makeDirectory($this->tmpDirectory, 0755, true);
            }
        }

        if (method_exists($this, 'refreshDatabase')) {
            $this->artisan('superv:install');
            config(['superv.installed' => true]);
            foreach ($this->installs as $droplet) {
                app(Installer::class)
                    ->setLocator(new Locator(realpath(__DIR__.'/../../../../')))
                    ->setSlug($droplet)
                    ->install();
            }

            (new PlatformServiceProvider($this->app))->boot();
        }
    }

    /**
     * @param string $slug
     * @param string $path
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    protected function setUpDroplet(
        $slug = 'superv.droplets.sample',
        $path = 'tests/Platform/__fixtures__/sample-droplet'
    ) {
        ComposerLoader::load(base_path($path));
        $this->app->make(Installer::class)
                  ->setSlug($slug)
                  ->setPath($path)
                  ->install();

        $entry = DropletModel::bySlug($slug);

        return $entry->resolveDroplet();
    }

    /**
     * @param       $port
     * @param       $hostname
     * @param null  $theme
     * @param array $roles
     * @param null  $model
     * @return void
     */
    protected function setUpPort($port, $hostname, $theme = null, $roles = [], $model = null)
    {
        Hub::register((new Port)->hydrate([
            'slug'     => $port,
            'hostname' => $hostname,
            'theme'    => $theme,
            'roles'    => $roles,
            'model'    => $model,
        ]));
//        $ports = [
//            $port => [
//                'hostname' => $hostname,
//                'theme'    => $theme,
//                'roles'    => $roles,
//                'model'    => $model,
//            ],
//        ];
//        config(['superv.ports' => $ports]);
    }

    protected function route($uri, $action, $port)
    {
        $port = \Hub::get($port);
        app(RouteRegistrar::class)->setPort($port)->registerRoute($uri, $action);
    }

    protected function tearDown()
    {
        if ($this->tmpDirectory) {
            app('files')->deleteDirectory(__DIR__.'/../tmp');
        }

        parent::tearDown();
    }

    /**
     * @param $abstract
     * @return \Mockery\Mock|\Mockery\MockInterface
     */
    protected function bindMock($abstract)
    {
        $this->app->instance($abstract, $mockInstance = \Mockery::mock($abstract));

        return $mockInstance;
    }

    protected function setUpPorts()
    {
        Hub::register(new class extends Port
        {
            protected $slug = 'web';

            protected $hostname = 'superv.io';

            protected $theme = 'themes.starter';
        });

        Hub::register(new class extends Port
        {
            protected $slug = 'acp';

            protected $hostname = 'superv.io';

            protected $prefix = 'acp';
        });

        Hub::register(new class extends Port
        {
            protected $slug = 'api';

            protected $hostname = 'api.superv.io';
        });
    }

    protected function assertProviderRegistered($provider)
    {
        $this->assertContains($provider, array_keys($this->app->getLoadedProviders()));
    }

    protected function assertProviderNotRegistered($provider)
    {
        $this->assertNotContains($provider, array_keys($this->app->getLoadedProviders()));
    }
}