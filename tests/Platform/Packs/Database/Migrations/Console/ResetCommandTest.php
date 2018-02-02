<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use SuperV\Platform\Packs\Database\Migrations\Console\ResetCommand;
use SuperV\Platform\Packs\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;
use Mockery as m;

class ResetCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    function reset_command_calls_migrator_with_proper_arguments()
    {
        $command = new ResetCommand(
            $migrator = m::mock(Migrator::class)->shouldIgnoreMissing()
        );
        $command->setLaravel($this->app);

        $migrator->shouldReceive('setScope')->with('test-scope')->once();

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