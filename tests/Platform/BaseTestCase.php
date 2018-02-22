<?php

namespace Tests\Platform;

use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\PlatformServiceProvider;
use Tests\ComposerLoader;
use Tests\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * Temporary directory to be created and
     * afterwards deleted in storage folder
     *
     * @var string
     */
    protected $tmpDirectory;

    /**
     * @param string $slug
     * @param string $path
     *
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    protected function setUpDroplet($slug = 'droplets.sample', $path = 'tests/Platform/__fixtures__/sample-droplet')
    {
        ComposerLoader::load(base_path($path));
        $this->app->make(Installer::class)
                  ->slug($slug)
                  ->path($path)
                  ->install();

        $entry = DropletModel::bySlug($slug);

        return $entry->resolveDroplet();
    }

    /**
     * @param       $port
     * @param       $hostname
     * @param null  $theme
     *
     * @param array $allowedUserTypes
     *
     * @return void
     */
    protected function setUpPort($port, $hostname, $theme = null, $allowedUserTypes = [], $model = null)
    {
        $ports = [
            $port => [
                'hostname'           => $hostname,
                'theme'              => $theme,
                'allowed_user_types' => $allowedUserTypes,
                'model'              => $model,
            ],
        ];
        config(['superv.ports' => $ports]);
    }

    protected function setUp()
    {
        parent::setUp();

        if ($this->tmpDirectory) {
            if (! file_exists(storage_path($this->tmpDirectory))) {
                app('files')->makeDirectory(storage_path($this->tmpDirectory));
            }
        }

        if (method_exists($this, 'refreshDatabase')) {
            $this->artisan('superv:install');
            config(['superv.installed' => true]);

            (new PlatformServiceProvider($this->app))->boot();
        }
    }

    protected function tearDown()
    {
        if ($this->tmpDirectory) {
            app('files')->deleteDirectory(storage_path($this->tmpDirectory));
        }

        parent::tearDown();
    }

    protected function setUpPorts()
    {
        return config([
            'superv.ports' => [
                'web' => [
                    'hostname' => 'superv.io',
                    'theme'    => 'themes.starter',
                ],
                'acp' => [
                    'hostname' => 'superv.io',
                    'prefix'   => 'acp',
                ],
                'api' => [
                    'hostname' => 'api.superv.io',
                ],
            ],
        ]);
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