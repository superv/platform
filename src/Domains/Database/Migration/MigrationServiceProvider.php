<?php

namespace SuperV\Platform\Domains\Database\Migration;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\MigrationServiceProvider as BaseMigrationServiceProvider;

class MigrationServiceProvider extends BaseMigrationServiceProvider
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
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }

    protected function registerCreator()
    {
        $this->app->singleton(
            'migration.creator',
            function ($app) {
                return new MigrationCreator($app['files']);
            }
        );
    }
}
