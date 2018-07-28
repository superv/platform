<?php

namespace Tests\Platform\Domains\Database\Migrations\Console;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Mockery as m;
use SuperV\Platform\Domains\Database\Migrations\Console\RefreshCommand;
use SuperV\Platform\Domains\Database\Migrations\Console\RollbackCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Tests\Platform\TestCase;
use Tests\Platform\TestsConsoleCommands;

class RefreshCommandTest extends TestCase
{
    use TestsConsoleCommands;

    /** @test */
    function refresh_command_calls_other_commands_with_proper_arguments_with_step()
    {
        $console = m::mock(ConsoleApplication::class)->makePartial();
        $console->__construct();

        $command = new RefreshCommand();
        $command->setLaravel($this->makeApplicationStub(['path.database' => __DIR__]));
        $command->setApplication($console);

        $console->shouldReceive('find')->with('migrate:rollback')->andReturn(
            $rollbackCommand = m::mock(RollbackCommand::class)
        );
        $console->shouldReceive('find')->with('migrate')->andReturn(
            $migrateCommand = m::mock(MigrateCommand::class)
        );

        $rollbackCommand->shouldReceive('run')->with(
            $this->makeInputMatcher("--database --path --realpath --step=2 --force --scope=test-scope 'migrate:rollback'"), m::any()
        );
        $migrateCommand->shouldReceive('run')->with(
            $this->makeInputMatcher('--database --path --realpath --force --scope=test-scope migrate'), m::any()
        );

        $this->runCommand($command, ['--step' => 2, '--scope' => 'test-scope']);
    }

    /** @test */
    function refresh_command_calls_other_commands_with_proper_arguments_without_step()
    {
        $console = m::mock(ConsoleApplication::class)->makePartial();
        $console->__construct();

        $command = new RefreshCommand();
        $command->setLaravel($this->makeApplicationStub(['path.database' => __DIR__]));
        $command->setApplication($console);

        $console->shouldReceive('find')->with('migrate:reset')
                ->andReturn($resetCommand = m::mock(ResetCommand::class));
        $console->shouldReceive('find')->with('migrate')
                ->andReturn($migrateCommand = m::mock(MigrateCommand::class));

        $resetCommand->shouldReceive('run')->with(
            $this->makeInputMatcher("--database --path --realpath --force --scope=test-scope 'migrate:reset'"), m::any()
        );
        $migrateCommand->shouldReceive('run')->with(
            $this->makeInputMatcher('--database --path --realpath --force --scope=test-scope migrate'), m::any()
        );

        $this->runCommand($command, ['--scope' => 'test-scope']);
    }
}


