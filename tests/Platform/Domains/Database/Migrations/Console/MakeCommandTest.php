<?php

namespace Tests\Platform\Domains\Database\Migrations\Console;

use SuperV\Platform\Domains\Database\Migrations\Scopes;
use Tests\Platform\TestCase;
use Tests\Platform\TestsConsoleCommands;

class MakeCommandTest extends TestCase
{
    use TestsConsoleCommands;

    protected $tmpDirectory = 'test-migrations';

//    /** @test */
//    function calls_creator_with_proper_arguments()
//    {
//        $command = new MigrateMakeCommand(
//            $creator = m::mock(MigrationCreator::class),
//            m::mock('Illuminate\Support\Composer')->shouldIgnoreMissing()
//        );
//        $command->setLaravel($this->app);
//
//        $creator->shouldReceive('setAddon')->with('test-addon')->once();
//        $creator->shouldReceive('create')->once();
//
//        $this->runCommand($command, ['name' => 'CreateMigrationMake', '--addon' => 'test-addon']);
//    }

    /**
     * @test
     * @group filesystem
     */
    function sets_path_from_registered_scopes()
    {
        Scopes::register('sample', $this->tmpDirectory);

        $this->assertCount(0, \File::files($this->tmpDirectory));
        $this->artisan('make:migration', ['name' => 'CreateMigrationMake', '--addon' => 'sample']);
        $this->assertCount(1, \File::files($this->tmpDirectory));
    }
}