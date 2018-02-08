<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use SuperV\Platform\Packs\Database\Migrations\Console\ResetCommand;
use SuperV\Platform\Packs\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;
use Mockery as m;
use Tests\SuperV\TestsConsoleCommands;

class ResetCommandTest extends BaseTestCase
{
    use TestsConsoleCommands;

    /** @test */
    function reset_command_calls_migrator_with_proper_arguments()
    {
        $command = new ResetCommand(
            $migrator = m::mock(Migrator::class)->shouldIgnoreMissing()
        );
        $command->setLaravel($this->app);

        $migrator->shouldReceive('setScope')->with('test-scope')->once();

        $this->runCommand($command, ['--scope' => 'test-scope']);
    }
}