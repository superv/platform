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

    /** @test */
    function rollback_command_calls_migrator_with_proper_arguments()
    {
        $command = new RollbackCommand(
            $migrator = m::mock(Migrator::class)->shouldIgnoreMissing()
        );
        $command->setLaravel($this->app);

        $migrator->shouldReceive('setAddon')->with('test-addon')->once();
        $migrator->shouldReceive('paths')->once()->andReturn([__DIR__.'/migrations']);
        $migrator->shouldReceive('setOutput')->once()->andReturnSelf();
        $migrator->shouldReceive('rollback')->once();
        $migrator->shouldReceive('getNotes')->andReturn([]);

        $this->runCommand($command, ['--addon' => 'test-addon']);
    }

    /** @test */
    function rollback_with_steps_and_scope()
    {
        $this->installSuperV();

        Scopes::register('foo', __DIR__.'/../migrations/foo');
        Scopes::register('baz', __DIR__.'/../migrations/baz');

        $this->artisan('migrate', ['--addon' => 'foo']);
        $this->assertDatabaseHas('migrations', ['addon' => 'foo']);

        $this->artisan('migrate:rollback', ['--addon' => 'foo']);
        $this->assertDatabaseMissing('migrations', ['addon' => 'foo']);
    }
}