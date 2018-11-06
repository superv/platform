<?php

namespace Tests\Platform\Domains\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Platform\TestCase;

class MigrationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function adds_column_addon_to_migrations_table()
    {
        $this->assertDatabaseHas('migrations', ['scope' => 'platform']);
    }
}