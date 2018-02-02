<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Packs\Database\Migrations\DatabaseMigrationRepository;
use SuperV\Platform\Packs\Database\Migrations\MigrationCreator;
use SuperV\Platform\Packs\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\Packs\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;

class MigrationServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function get_registered_with_platform()
    {
        $this->assertProviderRegistered(MigrationServiceProvider::class);
    }

    /**
     * @test
     */
    function registers_database_migration_repository()
    {
        $this->assertInstanceOf(\Illuminate\Database\MigrationServiceProvider::class, new MigrationServiceProvider($this->app));
        $this->assertInstanceOf(DatabaseMigrationRepository::class, $this->app['migration.repository']);
    }

    /**
     * @test
     */
    function registers_migrator()
    {
        $this->assertInstanceOf(Migrator::class, $this->app['migrator']);
    }

    /**
     * @test
     */
    function registers_migration_creator()
    {
        $this->assertInstanceOf(MigrationCreator::class, $this->app['migration.creator']);
    }
}