<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use SuperV\Platform\Packs\Database\Migrations\Console\MigrateMakeCommand;
use SuperV\Platform\Packs\Database\Migrations\MigrationCreator;
use Tests\SuperV\Platform\BaseTestCase;
use Mockery as m;

class MakeCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    function make_command_calls_creator_with_proper_arguments()
    {
        $command = new MigrateMakeCommand(
            $creator = m::mock(MigrationCreator::class),
            m::mock('Illuminate\Support\Composer')->shouldIgnoreMissing()
        );
        $creator->shouldReceive('setScope')->with('test-scope')->once();
        $creator->shouldReceive('create')->once();
        $command->setLaravel($this->app);
        $this->runCommand($command, ['name' => 'CreateMigrationMake', '--scope' => 'test-scope']);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(
            new \Symfony\Component\Console\Input\ArrayInput($input),
            new \Symfony\Component\Console\Output\NullOutput
        );
    }
}