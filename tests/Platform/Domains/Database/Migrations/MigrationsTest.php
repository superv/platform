<?php

namespace Tests\Platform\Domains\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Platform\TestCase;

class MigrationsTest extends TestCase
{
    use RefreshDatabase;

    protected $shouldInstallPlatform = false;

    function test__creates_migrations_table_with_scope_column()
    {
        $this->installSuperV();

        $this->assertDatabaseHas('migrations', ['scope' => 'platform']);
    }

    function test__adds_scope_column_to_existing_migrations_table()
    {
        $this->installSuperV();

        $this->assertDatabaseHas('migrations', ['scope' => 'platform']);
    }
}