<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use SuperV\Platform\Domains\Database\Migrations\Scopes;

class MigrateCommand extends \Illuminate\Database\Console\Migrations\MigrateCommand
{
    /** @var \SuperV\Platform\Domains\Database\Migrations\Migrator */
    protected $migrator;

    protected $signature = 'migrate {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--path=* : The path(s) to the migrations files to be executed}
                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                {--schema-path= : The path to a schema dump file}
                {--pretend : Dump the SQL queries that would be run}
                {--seed : Indicates if the seed task should be re-run}
                {--seeder= : The class name of the root seeder}
                {--step : Force the migrations to be run so they can be rolled back individually}
                {--namespace= : The namespace of migrations to be run.}';

    public function handle()
    {

        if ($this->option('namespace')) {
//            if (! $path = Scopes::path($this->option('namespace'))) {
//                $this->error('Migration namespace not registered');
//
//                return;
//            }
            $this->migrator->setNamespace($this->option('namespace'));
        }
        parent::handle();
    }

    protected function getMigrationPaths()
    {
        if ($this->option('namespace')) {
            if ($path = Scopes::path($this->option('namespace'))) {
                return [$path];
            }
        }

        $paths = parent::getMigrationPaths();

        return $paths;
    }
}
