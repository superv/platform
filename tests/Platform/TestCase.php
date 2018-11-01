<?php

namespace Tests\Platform;

use Hub;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Droplet\Locator;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\PlatformServiceProvider;
use SuperV\Platform\Testing\TestHelpers;

class TestCase extends OrchestraTestCase
{
    use TestHelpers;

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

    protected function getEnvironmentSetUp($app)
    {
        if (! empty($this->appConfig)) {
            $app['config']->set($this->appConfig);
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->withFactories(__DIR__.'/../database/factories');
        $this->makeTmpDirectory();

        $this->app->setBasePath(realpath(__DIR__.'/../../'));
        if (method_exists($this, 'refreshDatabase')) {
            $this->artisan('superv:install');
            config([
                'superv.installed' => true,
                'jwt.secret' => 'skdjfslkdfj'
            ]);

            $this->handlePostInstallCallbacks();

            foreach ($this->installs as $droplet) {
                app(Installer::class)
                    ->setLocator(new Locator(realpath(__DIR__.'/../../../../')))
                    ->setSlug($droplet)
                    ->install();
            }

            (new PlatformServiceProvider($this->app))->boot();
        }
    }

    protected function makeTmpDirectory(): void
    {
        if ($this->tmpDirectory) {
            $this->tmpDirectory = __DIR__.'/../tmp/'.$this->tmpDirectory;
            if (! file_exists($this->tmpDirectory)) {
                app('files')->makeDirectory($this->tmpDirectory, 0755, true);
            }
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

    protected function tearDown()
    {
        if ($this->tmpDirectory) {
            app('files')->deleteDirectory(__DIR__.'/../tmp');
        }

        parent::tearDown();
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

}