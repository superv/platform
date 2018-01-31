<?php

namespace SuperV\Platform\Packs\Database\Migrations;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;

class MigrationServiceProvider extends \Illuminate\Database\MigrationServiceProvider
{
    public function register()
    {
        $this->app->bind(MigrationRepositoryInterface::class, DatabaseMigrationRepository::class);
        parent::register();
    }

    protected function registerRepository()
    {
        $this->app->singleton('migration.repository', function ($app) {
            $table = $app['config']['database.migrations'];

            return new DatabaseMigrationRepository($app['db'], $table);
        });
    }

    protected function registerMigrator()
    {
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }
}