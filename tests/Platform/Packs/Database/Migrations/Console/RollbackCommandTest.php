<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use SuperV\Platform\Packs\Database\Migrations\Console\RollbackCommand;
use SuperV\Platform\Packs\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;
use Mockery as m;

class RollbackCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    function rollback_command_calls_migrator_with_proper_arguments()
    {
        $command = new RollbackCommand(
            $migrator = m::mock(Migrator::class)->shouldIgnoreMissing()
        );
        $migrator->shouldReceive('setScope')->with('test-scope')->once();
        $migrator->shouldReceive('paths')->once()->andReturn([__DIR__.'/migrations']);
        $migrator->shouldReceive('rollback')->once();
        $migrator->shouldReceive('getNotes')->andReturn([]);

        $command->setLaravel($this->app);
        $this->runCommand($command, ['--scope' => 'test-scope']);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(
            new \Symfony\Component\Console\Input\ArrayInput($input),
            new \Symfony\Component\Console\Output\NullOutput
        );
    }
}