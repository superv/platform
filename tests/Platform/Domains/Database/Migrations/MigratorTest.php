<?php

namespace Tests\Platform\Domains\Database\Migrations;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Migrations\Migrator;
use Tests\Platform\TestCase;

class MigratorTest extends TestCase
{
    use RefreshDatabase;

    function test__extends_original_migrator()
    {
        $migrator = new Migrator(app('migration.repository'), app('db'), app('files'));

        $this->assertInstanceOf(BaseMigrator::class, $migrator);
    }

//    function saves_migrations_scope_to_database()
//    {
//        Scopes::register('foo', __DIR__.'/migrations');
//        $this->app['migrator']->run(__DIR__.'/migrations');
//
//        $this->assertDatabaseHas('migrations', ['addon' => 'foo']);
//    }
//
//    function rollbacks_migrations_by_scope()
//    {
//        Scopes::register('foo', __DIR__.'/migrations');
//        $this->app['migrator']->run(__DIR__.'/migrations');
//        $this->assertDatabaseHas('migrations', ['addon' => 'foo']);
//
//        $this->app['migrator']->setAddon('foo')->rollback(__DIR__.'/migrations');
//        $this->assertDatabaseMissing('migrations', ['addon' => 'foo']);
//    }

    function runs_migrations_by_scope()
    {
//        $this->app['migrator']->setAddon('bar')->run(__DIR__.'/migrations');
//
//        $this->assertDatabaseMissing('migrations', ['addon' => 'foo']);
//        $this->assertDatabaseHas('migrations', ['addon' => 'bar']);
    }
}
