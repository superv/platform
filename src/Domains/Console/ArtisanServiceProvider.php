<?php

namespace SuperV\Platform\Domains\Console;

use SuperV\Platform\Domains\Database\Migration\Console\MigrateCommand;
use SuperV\Platform\Domains\Database\Migration\Console\MigrateMakeCommand;
use SuperV\Platform\Domains\Database\Migration\Console\RefreshCommand;
use SuperV\Platform\Domains\Database\Migration\Console\ResetCommand;
use SuperV\Platform\Domains\Database\Migration\Console\RollbackCommand;

/**
 * Class StreamsConsoleProvider
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class ArtisanServiceProvider extends \Illuminate\Foundation\Providers\ArtisanServiceProvider
{

    /**
     * The commands to register.
     *
     * @var array
     */
    protected $platformCommands = [

    ];

    protected function registerCommands(array $commands)
    {
        parent::registerCommands($commands);

    }

    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.migrate', function ($app) {
                return new MigrateCommand($app['migrator']);
            }
        );
    }

    protected function registerMigrateRollbackCommand()
    {
        $this->app->singleton('command.migrate.rollback', function ($app) {
            return new RollbackCommand($app['migrator']);
        });
    }

    protected function registerMigrateRefreshCommand()
    {
        $this->app->singleton('command.migrate.refresh', function () {
            return new RefreshCommand;
        });
    }

    protected function registerMigrateResetCommand()
    {
        $this->app->singleton('command.migrate.reset', function ($app) {
            return new ResetCommand($app['migrator']);
        });
    }

    protected function registerMigrateMakeCommand()
    {
        $this->app->singleton(
            'command.migrate.make',
            function ($app) {
                // Once we have the migration creator registered, we will create the command
                // and inject the creator. The creator is responsible for the actual file
                // creation of the migrations, and may be extended by these developers.
                $creator = $app['migration.creator'];

                $composer = $app['composer'];

                return new MigrateMakeCommand($creator, $composer);
            }
        );
    }
}
