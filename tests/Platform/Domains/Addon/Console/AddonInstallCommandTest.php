<?php

namespace Tests\Platform\Domains\Addon\Console;

use Mockery;
use SuperV\Platform\Domains\Addon\Installer;
use Tests\Platform\TestCase;

class AddonInstallCommandTest extends TestCase
{
    function test__calls_installer_with_proper_arguments_if_path_given()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldReceive('setCommand')->once()->andReturnSelf();
        $installer->shouldReceive('setPath')->with('path/to/addon')->once()->andReturnSelf();
        $installer->shouldReceive('setNamespace')->with('addon.slug')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('addon:install', [
            '--path' => 'path/to/addon',
            'addon'  => 'addon.slug',
        ]);
    }

    function test__calls_installer_with_proper_arguments_if_no_path_is_given()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldReceive('setCommand')->once()->andReturnSelf();
        $installer->shouldReceive('setLocator')->once()->andReturnSelf();
        $installer->shouldReceive('setNamespace')->with('vendor.addons.slug')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('addon:install', [
            'addon' => 'vendor.addons.slug',
        ]);
    }

    function test__runs_seeder_after_installation_if_provided()
    {
        $installer = $this->app->instance(Installer::class, Mockery::mock(Installer::class));
        $installer->shouldIgnoreMissing();
        $installer->shouldReceive('seed')->once();

        $this->artisan('addon:install', [
            'addon'  => 'vendor.addons.slug',
            '--seed' => true,
        ]);
    }
}
