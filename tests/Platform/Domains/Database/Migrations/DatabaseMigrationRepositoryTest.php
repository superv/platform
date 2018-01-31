<?php

namespace Tests\SuperV\Platform\Domains\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SuperV\TestCase;

class DatabaseMigrationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function filters_migrations_by_scope()
    {
        $repository = app('migration.repository');
        $table = config('database.migrations');

        \DB::table($table)->insert([
                ['migration' => '2018_0001_migration_A', 'batch' => 1, 'scope' => null],
                ['migration' => '2018_0002_migration_B', 'batch' => 2, 'scope' => 'foo'],
                ['migration' => '2018_0003_migration_C', 'batch' => 3, 'scope' => 'bar'],
                ['migration' => '2018_0004_migration_D', 'batch' => 4, 'scope' => 'foo'],
            ]
        );

        $migrations = collect($repository->setScope('foo')->getMigrations($steps = 4))
            ->map(function ($obj) {
                return ['migration' => $obj->migration];
            })->all();

        $this->assertArraySubset([
            ['migration' => '2018_0004_migration_D'],
            ['migration' => '2018_0002_migration_B'],
        ], $migrations);

        $this->assertCount(4, $repository->getMigrations($steps = 4));
        $this->assertCount(1, $repository->setScope('bar')->getMigrations($steps = 4));
    }
}