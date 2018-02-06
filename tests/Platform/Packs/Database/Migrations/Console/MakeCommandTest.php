<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use Mockery as m;
use SuperV\Platform\Packs\Database\Migrations\Console\MigrateMakeCommand;
use SuperV\Platform\Packs\Database\Migrations\MigrationCreator;
use Tests\SuperV\Platform\BaseTestCase;
use Tests\SuperV\TestsConsoleCommands;

class MakeCommandTest extends BaseTestCase
{
    use TestsConsoleCommands;

    protected $tmpDirectory = 'test-migrations';

    /**
     * @test
     */
    function calls_creator_with_proper_arguments()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            m::mock('Illuminate\Support\Composer')->shouldIgnoreMissing()
        );
        $command->setLaravel($this->app);

        $creator->shouldReceive('setScope')->with('test-scope')->once();
        $creator->shouldReceive('create')->once();

        $this->runCommand($command, ['name' => 'CreateMigrationMake', '--scope' => 'test-scope']);
    }

    /**
     * @test
     *
     * @group filesystem
     */
    function sets_path_from_registered_scopes()
    {
        config(['superv.migrations.scopes' => [
            'sample' => 'storage/test-migrations',
        ]]);

        $this->assertCount(0, \File::files(storage_path('test-migrations')));
        $this->artisan('make:migration', ['name' => 'CreateMigrationMake', '--scope' => 'sample']);
        $this->assertCount(1, \File::files(storage_path('test-migrations')));
    }

}