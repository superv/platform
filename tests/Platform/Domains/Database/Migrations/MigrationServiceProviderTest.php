<?php

namespace Tests\SuperV\Platform\Domains\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Migrations\DatabaseMigrationRepository;
use SuperV\Platform\Domains\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\Domains\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;

class MigrationServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function binds_database_migration_repository()
    {
        $this->assertInstanceOf(\Illuminate\Database\MigrationServiceProvider::class, new MigrationServiceProvider($this->app));
        $this->assertInstanceOf(DatabaseMigrationRepository::class, $this->app['migration.repository']);
    }

    /**
     * @test
     */
    function overrides_framework_migrator()
    {
        $this->assertInstanceOf(Migrator::class, $this->app['migrator']);
    }
}