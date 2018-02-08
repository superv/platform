<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery as m;
use SuperV\Platform\Packs\Database\Migrations\Console\MigrateCommand;
use SuperV\Platform\Packs\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;
use Tests\SuperV\TestsConsoleCommands;

class MigrateCommandTest extends BaseTestCase
{
    use RefreshDatabase;
    use TestsConsoleCommands;

    /** @test */
    function migrate_command_calls_migrator_with_proper_arguments()
    {
        $migrateCommand = new MigrateCommand(
            $migrator = m::mock(Migrator::class)->shouldIgnoreMissing()
        );
        $migrateCommand->setLaravel($this->app);

        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('getNotes')->once()->andReturn([]);
        $migrator->shouldReceive('setScope')->with('test-scope')->once();

        $this->runCommand($migrateCommand, ['--scope' => 'test-scope']);
    }

    /** @test */
    function migrate_command_get_path_from_registered_scopes()
    {
        config(['superv.migrations.scopes' => [
            'droplets.sample' => 'tests/Platform/__fixtures__/sample-droplet/database/migrations'
        ]]);

        $this->artisan('migrate', ['--scope' => 'droplets.sample']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200000_droplet_foo_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200100_droplet_bar_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200200_droplet_baz_migration']);
    }
}