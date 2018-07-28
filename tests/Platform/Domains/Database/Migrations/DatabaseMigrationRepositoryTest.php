<?php

namespace Tests\Platform\Domains\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Migrations\Scopes;
use Tests\Platform\TestCase;

class DatabaseMigrationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function filters_migrations_by_scope()
    {
        \DB::table(config('database.migrations'))->insert([
                ['migration' => '2018_0001_migration_A', 'batch' => 101, 'scope' => null],
                ['migration' => '2018_0002_migration_B', 'batch' => 102, 'scope' => 'foo'],
                ['migration' => '2018_0003_migration_C', 'batch' => 103, 'scope' => 'bar'],
                ['migration' => '2018_0004_migration_D', 'batch' => 104, 'scope' => 'foo'],
                ['migration' => '2018_0005_migration_E', 'batch' => 104, 'scope' => 'foo'],
            ]
        );

        $this->assertCount(5, $this->repositoryWithScope(null)->getMigrations($steps = 5));

        $this->assertCount(3, $this->repositoryWithScope('foo')->getMigrations($steps = 5));
        $this->assertArraySubset(
            [
                ['migration' => '2018_0005_migration_E'],
                ['migration' => '2018_0004_migration_D'],
                ['migration' => '2018_0002_migration_B'],
            ],
            $this->toArray($this->repositoryWithScope('foo')->getMigrations($steps = 3))
        );

        $this->assertCount(1, $this->repositoryWithScope('bar')->getMigrations($steps = 5));
    }

    /** @test */
    function filters_last_migrations_by_scope()
    {
        \DB::table(config('database.migrations'))->insert([
                ['migration' => '2018_0002_migration_B', 'batch' => 102, 'scope' => 'bar'],
                ['migration' => '2018_0003_migration_C', 'batch' => 103, 'scope' => 'bar'],
                ['migration' => '2018_0004_migration_D', 'batch' => 103, 'scope' => 'foo'],
                ['migration' => '2018_0005_migration_E', 'batch' => 103, 'scope' => 'foo'],
            ]
        );

        $this->assertCount(2, $this->repositoryWithScope('foo')->getLast());
        $this->assertArraySubset(
            [
                ['migration' => '2018_0005_migration_E'],
                ['migration' => '2018_0004_migration_D'],
            ],
            $this->toArray($this->repositoryWithScope('foo')->getLast())
        );

        $this->assertCount(1, $this->repositoryWithScope('bar')->getLast());
        $this->assertArraySubset(
            [
                ['migration' => '2018_0003_migration_C'],
            ],
            $this->toArray($this->repositoryWithScope('bar')->getLast())
        );

        $this->assertCount(2, $this->repositoryWithScope('foo')->getLast());
    }

    /** @test */
    function applies_scope_filter_on_ran_migrations()
    {
        Scopes::register('bar', __DIR__.'/migrations');
        $this->app['migrator']->run(__DIR__.'/migrations');

        $this->assertCount(2, $this->repositoryWithScope('bar')->getRan());
        $this->assertArraySubset(
            [
                "2016_01_01_200000_bar_scope_migration",
                "2016_01_01_200010_another_bar_scope_migration",
            ],
            $this->repositoryWithScope('bar')->getRan()
        );
    }

    /**
     * @param null $scope
     *
     * @return \SuperV\Platform\Domains\Database\Migrations\DatabaseMigrationRepository
     */
    protected function repositoryWithScope($scope = null)
    {
        return app('migration.repository')->setScope($scope);
    }

    protected function toArray($migrations)
    {
        return collect($migrations)
            ->map(function ($obj) {
                return ['migration' => $obj->migration];
            })->all();
    }
}