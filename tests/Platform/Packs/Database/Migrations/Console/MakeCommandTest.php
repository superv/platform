<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use SuperV\Platform\Packs\Database\Migrations\Console\MigrateMakeCommand;
use SuperV\Platform\Packs\Database\Migrations\MigrationCreator;
use Tests\SuperV\Platform\BaseTestCase;
use Mockery as m;
use Tests\SuperV\TestsConsoleCommands;

class MakeCommandTest extends BaseTestCase
{
    use TestsConsoleCommands;

    /**
     * @test
     */
    function make_command_calls_creator_with_proper_arguments()
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
}