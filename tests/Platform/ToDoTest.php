<?php

namespace Tests\Platform;

/**
 * Class ToDoTest
 *
 * Temporary location for todos
 *
 * @package Tests\Platform
 * @ignore
 */
class ToDoTest extends BaseTestCase
{
    /** @test */
    function current_global_handler()
    {
        $this->addToAssertionCount(1);
    }

    /** @test */
    function platform_detects_active_module_from_route_data()
    {
        $this->addToAssertionCount(1);
    }

    /** @test */
    function platform_add_view_hint_module_for_active_module()
    {
        $this->addToAssertionCount(1);
    }

    /** @test */
    function seeder_seeds_droplet_seeds()
    {
        $this->addToAssertionCount(1);
    }

    /** @test */
    function make_use_of_symfony_finder_while_interacting_with_files()
    {
        $this->addToAssertionCount(1);
    }
}