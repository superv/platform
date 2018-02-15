<?php

namespace Tests\SuperV\Platform\Domains\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SuperV\Platform\BaseTestCase;

class MigrationsTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function adds_column_droplet_to_migrations_table()
    {
        $this->assertDatabaseHas('migrations', ['scope' => null]);
    }
}