<?php

namespace SuperV\Platform\Testing;

use Current;
use Hub;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SuperV\Platform\Console\Jobs\InstallSuperV;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\PlatformServiceProvider;
use Tests\Platform\ComposerLoader;

class PlatformTestCase extends OrchestraTestCase
{
    use TestHelpers;

    /**
     * Temporary directory to be created in storage folder
     * during setup and deleted in tearDown
     *
     * @var string
     */
    protected $tmpDirectory;

    protected $packageProviders = [];

    protected $appConfig = [
        'app.key' => 'base64:SkW/b3Bg7pb2vvIOad6noSrFSR7eUS8ZdCXl0LoRQNI=',
    ];

    protected $shouldInstallPlatform = true;

    protected $shouldBootPlatform = false;

    protected $installs = [];

    protected $handleExceptions = true;

    protected $basePath;

    protected $bindings = [];

    public function basePath($path = null)
    {
        return __DIR__.($path ? '/'.$path : '');
    }

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
        ]);

        $app['config']->set('database.connections.sqlite2', [
            'driver'   => 'sqlite',
            'database' => $this->basePath('sv-testing.sqlite'),
        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        if ($this->handleExceptions === false) {
            $this->withoutExceptionHandling();
        }

        $this->loadLaravelMigrations();

        $this->makeTmpDirectory();

        $this->setUpMacros();

        if ($this->shouldInstallPlatform()) {
            $this->installSuperV();
        }

        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
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

    protected function setUpAndSeedAddon($identifier = null)
    {
        return $this->setUpAddon($identifier, true);
    }

    protected function setUpAddon($identifier = null, $seed = false): Addon
    {
        $identifier = $identifier ?? 'superv.sample';

        $installer = $this->setUpAddonInstaller($identifier);
        $installer->install();
        if ($seed === true) {
            $installer->seed();
        }

        $entry = AddonModel::byIdentifier($identifier);

        return $entry->resolveAddon();
    }

    protected function setUpPorts()
    {
        Hub::register(new class extends Port
        {
            protected $slug = 'web';

            protected $hostname = 'superv.io';
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

    protected function installAddons(): void
    {
        $this->app->setBasePath($basePath = $this->basePath ?? realpath(__DIR__.'/../../../../../'));

        foreach ($this->installs as $addonPath) {
            Installer::resolve()
                     ->setPath($basePath.'/'.$addonPath)
                     ->install();
        }
    }

    protected function setConfigParams(): void
    {
        config([
            'superv.installed' => true,
            'jwt.secret'       => 'skdjfslkdfj',
        ]);
    }

    protected function installSuperV(): void
    {
        InstallSuperV::dispatch();

        $this->setConfigParams();

        $this->handlePostInstallCallbacks();

        if ($this->shouldBootPlatform) {
            (new PlatformServiceProvider($this->app))->boot();
        }

        $this->installAddons();
    }

    protected function shouldInstallPlatform()
    {
        return $this->shouldInstallPlatform && method_exists($this, 'refreshDatabase');
    }

    protected function setUpAddonInstaller($identifier, $path = null): Installer
    {
        $path = $path ?? 'tests/Platform/__fixtures__/sample-addon';

        ComposerLoader::load(base_path($path));
        $installer = Installer::resolve()
                              ->setPath(base_path($path))
                              ->setIdentifier($identifier);

        return $installer;
    }
}
