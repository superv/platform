<?php

namespace Tests\Platform\Console;

use Event;
use Platform;
use SuperV\Platform\Console\Jobs\InstallSuperV;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Events\PlatformInstalledEvent;
use SuperV\Platform\Testing\TestHelpers;
use Tests\Platform\TestCase;
use Tests\Platform\TestsConsoleCommands;

class SuperVInstallCommandTest extends TestCase
{
    use TestsConsoleCommands;
    use TestHelpers;


    function test_sets_env_variables()
    {
        file_put_contents(base_path('.env'), $this->envPath('sample'));

        InstallSuperV::dispatch();

        $envFile = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('SV_INSTALLED=true', $envFile);
        $this->assertStringContainsString('SV_HOSTNAME=localhost', $envFile);
    }

    function test_sets_env_variable_existing_parameter()
    {
        file_put_contents(base_path('.env'), $this->envPath('existing'));

        InstallSuperV::dispatch();

        $envFile = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('SV_INSTALLED=true', $envFile);
        $this->assertStringContainsString('SV_HOSTNAME=localhost', $envFile);
    }

    function test_sets_env_variable_invalid_parameter()
    {
        file_put_contents(base_path('.env'), 'SV_INSTALLED***invalid');

        InstallSuperV::dispatch();

        $envFile = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('SV_INSTALLED=true', $envFile);
        $this->assertStringContainsString('SV_HOSTNAME=localhost', $envFile);
    }

    function test_sets_env_variable_empty_env()
    {
        file_put_contents(base_path('.env'), '');

        $this->artisan('superv:install', ['--hostname' => 'my-hostname']);

        $envFile = file_get_contents(base_path('.env'));
        $this->assertStringContainsString('SV_INSTALLED=true', $envFile);
        $this->assertStringContainsString('SV_HOSTNAME=my-hostname', $envFile);
    }

    function test_does_not_make_any_other_changes_on_env_file()
    {
        file_put_contents(base_path('.env'), file_get_contents($this->envPath('sample')));

        InstallSuperV::dispatch();

        $this->assertEnvValuesPreserved($this->envPath('sample'), base_path('.env'));
    }

    function test_runs_platform_migrations()
    {
        InstallSuperV::dispatch();

        $this->assertEquals(0, AddonModel::count());
    }

    function test_dispatches_event_when_platform_is_installed()
    {
        Event::fake([PlatformInstalledEvent::class]);

        InstallSuperV::dispatch();

        Event::assertDispatched(PlatformInstalledEvent::class);
    }

    function test_runs_callbacks_when_platform_is_installed()
    {
        $_SERVER['__switch__'] = 'off';

        Platform::on('install', function () {
            $_SERVER['__switch__'] = 'on';
        });

        InstallSuperV::dispatch();

        $this->assertEquals('on', $_SERVER['__switch__']);
    }

    function test__adds_namespace_column_to_migrations_table()
    {
        InstallSuperV::dispatch();

        $this->assertColumnExists('migrations', 'namespace');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setBasePath(base_path('tests'));
    }

    protected function tearDown(): void
    {
        file_put_contents(base_path('.env'), '');

        $this->setBasePath();

        parent::tearDown();
    }

    protected function envPath($name)
    {
        return __DIR__.'/../__fixtures__/'.$name.'.env';
    }

    protected function assertEnvValuesPreserved($orig, $updated)
    {
        $orig = file($orig);
        $updated = file($updated);

        $this->assertEquals(count($orig) + 2, count($updated));

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
