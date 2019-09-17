<?php

namespace Tests\Platform\Domains\Addon\Console;

use Mockery;
use SuperV\Platform\Domains\Addon\Installer;

class AddonInstallCommandTest
{
    function test__calls_installer_with_proper_arguments_if_path_given()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldReceive('setCommand')->once()->andReturnSelf();
        $installer->shouldReceive('setPath')->with('path/to/addon')->once()->andReturnSelf();
        $installer->shouldReceive('setVendor')->with('superv')->once()->andReturnSelf();
        $installer->shouldReceive('setName')->with('sample')->once()->andReturnSelf();
        $installer->shouldReceive('setAddonType')->with('drop')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('addon:install', [
            'vendor'  => 'superv',
            'package' => 'sample',
            '--path'  => 'path/to/addon',
            '--type'  => 'drop',
        ]);
    }

    function test__calls_installer_with_proper_arguments_if_no_path_is_given()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldReceive('setCommand')->once()->andReturnSelf();
        $installer->shouldReceive('setVendor')->with('superv')->once()->andReturnSelf();
        $installer->shouldReceive('setName')->with('sample')->once()->andReturnSelf();
        $installer->shouldReceive('setAddonType')->with('module')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('addon:install', [
            'vendor'  => 'superv',
            'package' => 'sample',
        ]);
    }

    function test__runs_seeder_after_installation_if_provided()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldIgnoreMissing();
        $installer->shouldReceive('seed')->once();

        $this->artisan('addon:install', [
            'vendor'  => 'superv',
            'package' => 'sample',
            '--seed'  => true,
        ]);
    }
}
