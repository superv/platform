<?php

namespace Tests\SuperV\Platform;

use SuperV\Platform\Packs\Droplet\DropletModel;
use SuperV\Platform\Packs\Droplet\Installer;
use Tests\SuperV\TestCase;

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
     * @return \SuperV\Platform\Packs\Droplet\Droplet
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    protected function setUpDroplet($slug = 'droplets.sample', $path = 'tests/Platform/__fixtures__/sample-droplet')
    {
        $this->app->make(Installer::class)
                  ->slug($slug)
                  ->path($path)
                  ->install();

        $entry = DropletModel::bySlug($slug);

        return $entry->resolveDroplet();
    }

    /**
     * @param      $port
     * @param      $hostname
     * @param null $theme
     */
    protected function setUpPort($port, $hostname, $theme = null)
    {
        $ports = config('superv.ports', []);
        $ports[$port] = [
            'hostname' => $hostname,
            'theme'    => $theme,
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
            $this->artisan('migrate', ['--scope' => 'platform']);
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
}