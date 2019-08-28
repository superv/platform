<?php

namespace Tests\Platform\Domains\Database\Migrations\Console;

use Mockery as m;
use SuperV\Platform\Domains\Database\Migrations\Console\RollbackCommand;
use SuperV\Platform\Domains\Database\Migrations\Migrator;
use SuperV\Platform\Domains\Database\Migrations\Scopes;
use Tests\Platform\TestCase;
use Tests\Platform\TestsConsoleCommands;

class RollbackCommandTest extends TestCase
{
    use TestsConsoleCommands;

    function test__rollback_command_calls_migrator_with_proper_arguments()
    {
        $command = new RollbackCommand(
            $migrator = m::mock(Migrator::class)->shouldIgnoreMissing()
        );
        $command->setLaravel($this->app);

        $migrator->shouldReceive('setNamespace')->with('test-addon')->once();
        $migrator->shouldReceive('paths')->once()->andReturn([__DIR__.'/migrations']);
        $migrator->shouldReceive('setOutput')->once()->andReturnSelf();
        $migrator->shouldReceive('rollback')->once();

        $this->runCommand($command, ['--namespace' => 'test-addon']);
    }

    function test__rollback_with_steps_and_scope()
    {
        $this->installSuperV();

        Scopes::register('foo', __DIR__.'/../migrations/foo');
        Scopes::register('baz', __DIR__.'/../migrations/baz');

        $this->artisan('migrate', ['--namespace' => 'foo']);
        $this->assertDatabaseHas('migrations', ['namespace' => 'foo']);

        $this->artisan('migrate:rollback', ['--namespace' => 'foo']);
        $this->assertDatabaseMissing('migrations', ['namespace' => 'foo']);
    }
}
