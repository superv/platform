<?php

namespace Tests\Platform\Domains\Droplet\Console;

use Mockery;
use SuperV\Platform\Domains\Addon\Installer;
use Tests\Platform\TestCase;

class DropletInstallCommandTest extends TestCase
{
    /** @test */
    function calls_installer_with_proper_arguments_if_path_given()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldReceive('setCommand')->once()->andReturnSelf();
        $installer->shouldReceive('setPath')->with('path/to/droplet')->once()->andReturnSelf();
        $installer->shouldReceive('setSlug')->with('droplet.slug')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('droplet:install', [
            '--path' => 'path/to/droplet',
            'slug'   => 'droplet.slug',
        ]);
    }

    /** @test */
    function calls_installer_with_proper_arguments_if_no_path_is_given()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldReceive('setCommand')->once()->andReturnSelf();
        $installer->shouldReceive('setLocator')->once()->andReturnSelf();
        $installer->shouldReceive('setSlug')->with('vendor.addons.slug')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('droplet:install', [
            'slug' => 'vendor.addons.slug',
        ]);
    }
}