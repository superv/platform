<?php

namespace Tests\Platform\Console;

use SuperV\Platform\Domains\Droplet\DropletModel;
use Tests\Platform\TestCase;
use Tests\Platform\TestsConsoleCommands;

class SuperVInstallCommandTest extends TestCase
{
    use TestsConsoleCommands;

    protected function setUp()
    {
        parent::setUp();

        $this->app->setBasePath(base_path('tests'));
    }

    /** @test */
    function sets_env_variable_existing_parameter()
    {
        file_put_contents(base_path('.env'), 'SV_INSTALLED=false');

        $this->artisan('superv:install');
        $this->assertContains('SV_INSTALLED=true', file_get_contents(base_path('.env')));
    }

    /** @test */
    function sets_env_variable_invalid_parameter()
    {
        file_put_contents(base_path('.env'), 'SV_INSTALLED***invalid');

        $this->artisan('superv:install');

        $this->assertContains('SV_INSTALLED=true', file_get_contents(base_path('.env')));
    }

    /** @test */
    function sets_env_variable_empty_env()
    {
        file_put_contents(base_path('.env'), '');

        $this->artisan('superv:install');

        $this->assertContains('SV_INSTALLED=true', file_get_contents(base_path('.env')));
    }

    /** @test */
    function does_not_make_any_other_changes_on_env_file()
    {
        file_put_contents(base_path('.env'), file_get_contents($this->envPath('sample')));

        $this->artisan('superv:install');

        $this->assertEnvValuesPreserved($this->envPath('sample'), base_path('.env'));
    }

    /** @test */
    function runs_platform_migrations()
    {
        $this->artisan('superv:install');

        $this->assertEquals(0, DropletModel::count());
    }

    protected function envPath($name)
    {
        return __DIR__.'/../__fixtures__/'.$name.'.env';
    }

    private function getOrigEnvFile()
    {
        return file_get_contents(__DIR__.'../sample.env');

        return [
            'APP_ENV=local'."\r\n",
            'APP_KEY='."\r\n",
            'APP_URL=http://localhost'."\r\n",
            ''."\r\n",
            'LOG_CHANNEL=stack'."\r\n",
            ''."\r\n",
            'DB_CONNECTION=mysql'."\r\n",
            'DB_HOST=127.0.0.1'."\r\n",
        ];
    }

    protected function tearDown()
    {
        parent::tearDown();

        file_put_contents(base_path('.env'), '');
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