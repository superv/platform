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
class ToDoTest extends TestCase
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
    function installer_uninstalls_subaddons_when_a_droplet_is_uninstalled()
    {
        $this->addToAssertionCount(1);
    }
}