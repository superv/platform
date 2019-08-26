<?php

namespace Tests\Platform\Domains\Database\Migrations\Console;

use Mockery as m;
use SuperV\Platform\Domains\Database\Migrations\Console\ResetCommand;
use SuperV\Platform\Domains\Database\Migrations\Migrator;
use Tests\Platform\TestCase;
use Tests\Platform\TestsConsoleCommands;

class ResetCommandTest extends TestCase
{
    use TestsConsoleCommands;

    function test__reset_command_calls_migrator_with_proper_arguments()
    {
        $command = new ResetCommand(
            $migrator = m::mock(Migrator::class)->shouldIgnoreMissing()
        );
        $command->setLaravel($this->app);

        $migrator->shouldReceive('setNamespace')->with('test-addon')->once();

        $this->runCommand($command, ['--namespace' => 'test-addon']);
    }
}
