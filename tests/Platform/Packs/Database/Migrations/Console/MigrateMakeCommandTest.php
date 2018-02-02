<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use SuperV\Platform\Packs\Database\Migrations\Console\MigrateMakeCommand;
use SuperV\Platform\Packs\Database\Migrations\MigrationCreator;
use Tests\SuperV\Platform\BaseTestCase;
use Mockery as m;

class MigrateMakeCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    function configures_creator_if_scope_option_is_provided()
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