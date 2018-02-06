<?php

namespace Tests\SuperV\Platform\Packs\Droplet\Console;

use Mockery;
use SuperV\Platform\Packs\Droplet\Installer;
use Tests\SuperV\Platform\BaseTestCase;

class DropletInstallCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    function calls_installer_with_proper_arguments()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldReceive('path')->with('path/to/droplet')->once()->andReturnSelf();
        $installer->shouldReceive('slug')->with('droplet.slug')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('droplet:install', [
            '--path' => 'path/to/droplet',
            'slug'   => 'droplet.slug',
        ]);
    }
}