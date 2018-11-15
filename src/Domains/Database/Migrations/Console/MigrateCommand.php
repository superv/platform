<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use SuperV\Platform\Domains\Database\Migrations\Scopes;

class MigrateCommand extends \Illuminate\Database\Console\Migrations\MigrateCommand
{
    protected $signature = 'migrate {--database= : The database connection to use.}
                {--force : Force the operation to run when in production.}
                {--path= : The path of migrations files to be executed.}
                {--pretend : Dump the SQL queries that would be run.}
                {--seed : Indicates if the seed task should be re-run.}
                {--step : Force the migrations to be run so they can be rolled back individually.}
                {--scope= : Scope of migrations to be run.}';

    public function handle()
    {
        if ($this->option('scope')) {
            if (! $path = Scopes::path($this->option('scope'))) {
                $this->error('Scope not registered');

                return;
            }
            $this->migrator->setScope($this->option('scope'));
        }
        parent::handle();
    }

    protected function getMigrationPaths()
    {
        if ($this->option('scope')) {
            if ($path = Scopes::path($this->option('scope'))) {
                return [$path];
            }
        }

        $paths = parent::getMigrationPaths();

        return $paths;
    }
}