<?php

namespace Tests\Platform\Domains\Database\Migrations\Console;

use SuperV\Platform\Domains\Database\Migrations\Scopes;
use Tests\Platform\TestCase;
use Tests\Platform\TestsConsoleCommands;

class MakeCommandTest extends TestCase
{
    use TestsConsoleCommands;

    protected $tmpDirectory = 'test-migrations';

    /**
     * @test
     * @group filesystem
     */
    function sets_path_from_registered_scopes()
    {
        Scopes::register('sample', $this->tmpDirectory);

        $this->assertCount(0, \File::files($this->tmpDirectory));
        $this->artisan('make:migration', ['name' => 'CreateMigrationMake', '--namespace' => 'sample']);
        $this->assertCount(1, \File::files($this->tmpDirectory));
    }
}
