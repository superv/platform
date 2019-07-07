<?php

namespace Tests\Platform;

use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ToDoTest
 * Temporary location for todos
 *
 * @package Tests\Platform
 * @ignore
 */
class ToDoTest extends TestCase
{
    use RefreshDatabase;

    function test__pdo()
    {
        $conn = DB::connection()->getDoctrineConnection();

        $this->newUser();
        $this->assertEquals(1, count($conn->fetchAll("SELECT * FROM users")));
    }

    function test__platform_detects_active_module_from_route_data()
    {
        $this->addToAssertionCount(1);
    }

    function test__platform_add_view_hint_module_for_active_module()
    {
        $this->addToAssertionCount(1);
    }

    function test__installer_uninstalls_subaddons_when_a_addon_is_uninstalled()
    {
        $this->addToAssertionCount(1);
    }
}