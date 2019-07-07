<?php

namespace Tests\Platform\Console;

use Event;
use Illuminate\Support\Facades\Schema;
use Platform;
use SuperV\Platform\Domains\Addon\Events\AddonUninstallingEvent;
use Tests\Platform\TestCase;

class SuperVUninstallCommandTest extends TestCase
{
    function test__drops_platform_tables()
    {
        $this->artisan('superv:install');
        foreach (Platform::tables() as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->artisan('superv:uninstall');
        foreach (Platform::tables() as $table) {
            $this->assertFalse(Schema::hasTable($table), $table);
        }
    }

    function test__cleans_up_env_file()
    {
        $this->app->setBasePath(base_path('tests'));
        file_put_contents(base_path('.env'), '');

        $this->artisan('superv:install');
        $this->assertContains('SV_INSTALLED=true', file_get_contents(base_path('.env')));
        $this->artisan('superv:uninstall');
        $this->assertContains('SV_INSTALLED=false', file_get_contents(base_path('.env')));
    }

    function test__uninstalls_modules()
    {
        $this->artisan('superv:install');

        $this->setUpAddon();

        Event::fake(AddonUninstallingEvent::class);

        $this->artisan('superv:uninstall');

        Event::assertDispatched(AddonUninstallingEvent::class);
    }

    protected function envPath($name)
    {
        return __DIR__.'/../__fixtures__/'.$name.'.env';
    }

    protected function assertEnvValuesPreserved($orig, $updated)
    {
        $orig = file($orig);
        $updated = file($updated);

        foreach ($orig as $line) {
            if (starts_with($line, 'SV_')) {
                continue;
            }
            if (! in_array($line, $updated)) {
                $this->fail('Failed to assert previous env values are preserved');
            }
        }

        $this->addToAssertionCount(1);
    }
}