<?php

namespace Tests\Platform;

use Current;
use Hub;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Addon\Locator;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
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

    protected $handleExceptions = true;

    protected function getPackageProviders($app)
    {
        return array_flatten(array_merge([PlatformServiceProvider::class], $this->packageProviders));
    }

    protected function getEnvironmentSetUp($app)
    {
        if (! empty($this->appConfig)) {
            $app['config']->set($this->appConfig);
        }

        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        if ($this->handleExceptions === false) {
            $this->withoutExceptionHandling();
        }

        $this->loadLaravelMigrations();
        $this->withFactories(__DIR__.'/../database/factories');
        $this->makeTmpDirectory();
        $this->setUpMacros();

        $this->app->setBasePath(realpath(__DIR__.'/../../'));
        if (method_exists($this, 'refreshDatabase')) {
            $this->artisan('superv:install');
            config([
                'superv.installed' => true,
                'jwt.secret'       => 'skdjfslkdfj',
            ]);

            $this->handlePostInstallCallbacks();

            foreach ($this->installs as $addon) {
                app(Installer::class)
                    ->setLocator(new Locator(realpath(__DIR__.'/../../../../')))
                    ->setSlug($addon)
                    ->install();
            }

            (new PlatformServiceProvider($this->app))->boot();
        }
//        define('SV_TEST_BASE', $this->basePath());
    }

    protected function tearDown()
    {
        if ($this->tmpDirectory) {
            app('files')->deleteDirectory(__DIR__.'/../tmp');
        }

        parent::tearDown();
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
     * @return \SuperV\Platform\Domains\Addon\Addon
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    protected function setUpAddon(
        $slug = 'superv.addons.sample',
        $path = 'tests/Platform/__fixtures__/sample-addon'
    ) {
        ComposerLoader::load(base_path($path));
        $this->app->make(Installer::class)
                  ->setSlug($slug)
                  ->setPath($path)
                  ->install();

        $entry = AddonModel::bySlug($slug);

        return $entry->resolveAddon();
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

    /**
     * Setup and Activate a test port
     *
     * @param      $hostname
     * @param null $prefix
     * @return \SuperV\Platform\Domains\Port\Port
     */
    protected function setUpCustomPort($hostname, $prefix = null)
    {
        $port = $this->setUpPort(['slug' => 'api', 'hostname' => $hostname, 'prefix' => $prefix]);
        PortDetectedEvent::dispatch($port);

        return $port;
    }

    public function basePath($path = null)
    {
        return __DIR__.($path ? '/'.$path : '');
    }

    protected function makeRequest($path = null)
    {
        $this->app->extend('request', function () use ($path) {
            return Request::create('http://'.Current::port()->root().($path ? '/'.$path : ''));
        });
    }

    protected function makeUploadedFile($filename = 'square.png')
    {
        return new UploadedFile($this->basePath('__fixtures__/'.$filename), $filename);
    }
}