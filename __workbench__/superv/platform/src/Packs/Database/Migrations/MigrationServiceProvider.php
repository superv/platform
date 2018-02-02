<?php

namespace SuperV\Platform\Packs\Database\Migrations;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use SuperV\Platform\Packs\Database\Migrations\Console\MigrateCommand;
use SuperV\Platform\Packs\Database\Migrations\Console\MigrateMakeCommand;
use SuperV\Platform\Packs\Database\Migrations\Console\RefreshCommand;
use SuperV\Platform\Packs\Database\Migrations\Console\ResetCommand;
use SuperV\Platform\Packs\Database\Migrations\Console\RollbackCommand;

class MigrationServiceProvider extends \Illuminate\Database\MigrationServiceProvider
{
    public function register()
    {
        $this->registerRepository();

        $this->registerMigrator();

        $this->registerCreator();

        $this->extendMigrationRepository();

        $this->extendConsoleCommands();
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

    protected function registerCreator()
    {
        $this->app->singleton('migration.creator', function ($app) {
             return new MigrationCreator($app['files']);
         });
    }

    protected function extendConsoleCommands(): void
    {
        $this->app->extend(
            'command.migrate.make',
            function ($command, $app) {
                return new MigrateMakeCommand($app['migration.creator'], $app['composer']);
            }
        );

        $this->app->extend(
            'command.migrate',
            function ($command, $app) {
                return new MigrateCommand($app['migrator']);
            }
        );

        $this->app->extend(
            'command.migrate.rollback',
            function ($command, $app) {
                return new RollbackCommand($app['migrator']);
            }
        );

        $this->app->extend(
            'command.migrate.reset',
            function ($command, $app) {
                return new ResetCommand($app['migrator']);
            }
        );

        $this->app->extend(
            'command.migrate.refresh',
            function () {
                return new RefreshCommand();
            }
        );
    }

    protected function extendMigrationRepository(): void
    {
        $this->app->bind(MigrationRepositoryInterface::class, DatabaseMigrationRepository::class);
    }
}