<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SuperV\Platform\BaseTestCase;

class MigrationMakeCommandTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function can_set_scope_parameter_on_migration_file()
    {
        // dive into MigrationCreator Test..
        // 1 - override stub path
        // 2 - parse scope while populating stub
        // 3 - 
    }
}