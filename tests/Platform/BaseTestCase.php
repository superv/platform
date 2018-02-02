<?php

namespace Tests\SuperV\Platform;

use Mockery as m;
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
     * @return \SuperV\Platform\Packs\Droplet\Droplet
     */
    protected function setUpDroplet()
    {
        app(Installer::class)
            ->slug('droplets.sample')
            ->path('tests/Platform/__fixtures__/sample-droplet')
            ->install();

        $entry = DropletModel::bySlug('droplets.sample');

        return $entry->resolveDroplet();
    }

    protected function setUpMock($class)
    {
        $mock = m::mock($class);
        $this->app->singleton($class, function () use ($mock) { return $mock; });

        return $mock;
    }

    protected function setUp()
    {
        parent::setUp();

        if ($this->tmpDirectory) {
            if (! file_exists(storage_path($this->tmpDirectory))) {
                app('files')->makeDirectory(storage_path($this->tmpDirectory));
            }
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
                    'theme' => 'themes.starter'
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