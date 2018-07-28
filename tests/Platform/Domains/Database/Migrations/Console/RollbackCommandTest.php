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

        $migrator->shouldReceive('setScope')->with('test-scope')->once();
        $migrator->shouldReceive('paths')->twice()->andReturn([__DIR__.'/migrations']);
        $migrator->shouldReceive('rollback')->once();
        $migrator->shouldReceive('getNotes')->andReturn([]);

        $this->runCommand($command, ['--scope' => 'test-scope']);
    }

    /** @test */
    function rollback_with_steps_and_scope()
    {
        Scopes::register('foo', __DIR__.'/../migrations/foo');
        Scopes::register('baz', __DIR__.'/../migrations/baz');

        $this->artisan('migrate', ['--scope' => 'foo']);
        $this->assertDatabaseHas('migrations', ['scope' => 'foo']);

        $this->artisan('migrate:rollback', ['--scope' => 'foo']);
        $this->assertDatabaseMissing('migrations', ['scope' => 'foo']);
    }
}