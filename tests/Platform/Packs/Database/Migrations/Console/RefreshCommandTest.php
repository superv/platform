<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Foundation\Application;
use Mockery as m;
use SuperV\Platform\Packs\Database\Migrations\Console\RefreshCommand;
use SuperV\Platform\Packs\Database\Migrations\Console\RollbackCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Tests\SuperV\Platform\BaseTestCase;

class RefreshCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    function refresh_command_calls_other_commands_with_proper_arguments_with_step()
    {
        $command = new RefreshCommand();

        $app = new ApplicationDatabaseRefreshStub(['path.database' => __DIR__]);
        $console = m::mock(ConsoleApplication::class)->makePartial();
        $console->__construct();
        $command->setLaravel($app);
        $command->setApplication($console);

        $console->shouldReceive('find')->with('migrate:rollback')->andReturn($rollbackCommand = m::mock(RollbackCommand::class));
        $console->shouldReceive('find')->with('migrate')->andReturn($migrateCommand = m::mock(MigrateCommand::class));

        $rollbackCommand->shouldReceive('run')->with(
            new InputMatcher("--database --path --step=2 --force --scope=test-scope 'migrate:rollback'"), m::any()
        );
        $migrateCommand->shouldReceive('run')->with(
            new InputMatcher('--database --path --force --scope=test-scope migrate'), m::any()
        );

        $this->runCommand($command, ['--step' => 2, '--scope' => 'test-scope']);
    }

    /**
     * @test
     */
    function refresh_command_calls_other_commands_with_proper_arguments_without_step()
    {
        $command = new RefreshCommand();

        $app = new ApplicationDatabaseRefreshStub(['path.database' => __DIR__]);
        $console = m::mock(ConsoleApplication::class)->makePartial();
        $console->__construct();
        $command->setLaravel($app);
        $command->setApplication($console);

        $console->shouldReceive('find')->with('migrate:reset')
                                       ->andReturn($resetCommand = m::mock(ResetCommand::class));
        $console->shouldReceive('find')->with('migrate')
                                       ->andReturn($migrateCommand = m::mock(MigrateCommand::class));

        $resetCommand->shouldReceive('run')->with(
            new InputMatcher("--database --path --force --scope=test-scope 'migrate:reset'"), m::any()
        );
        $migrateCommand->shouldReceive('run')->with(
            new InputMatcher('--database --path --force --scope=test-scope migrate'), m::any()
        );

        $this->runCommand($command, [ '--scope' => 'test-scope']);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(
            new \Symfony\Component\Console\Input\ArrayInput($input),
            new \Symfony\Component\Console\Output\NullOutput
        );
    }
}

class InputMatcher extends m\Matcher\MatcherAbstract
{
    /**
     * @param  \Symfony\Component\Console\Input\ArrayInput  $actual
     * @return bool
     */
    public function match(&$actual)
    {
        return (string) $actual == $this->_expected;
    }

    public function __toString()
    {
        return '';
    }
}

class ApplicationDatabaseRefreshStub extends Application
{
    public function __construct(array $data = [])
    {
        foreach ($data as $abstract => $instance) {
            $this->instance($abstract, $instance);
        }
    }

    public function environment()
    {
        return 'development';
    }
}