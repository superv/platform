<?php

namespace SuperV\Platform\Domains\Database\Migrations\Console;

use SuperV\Platform\Domains\Database\Migrations\Scopes;

class MigrateMakeCommand extends \Illuminate\Database\Console\Migrations\MigrateMakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:migration {name : The name of the migration.}
        {--create= : The table to be created.}
        {--table= : The table to migrate.}
        {--path= : The location where the migration file should be created.}
        {--scope= : The migration scope.}
        ';

    /** @var \SuperV\Platform\Domains\Database\Migrations\MigrationCreator */
    protected $creator;

    public function handle()
    {
        if ($this->option('scope')) {
            $this->creator->setScope($this->option('scope'));
        }
        parent::handle();
    }

    protected function getMigrationPath()
    {
        if ($this->option('scope')) {

            if ($path = Scopes::path($this->option('scope'))) {
                $this->creator->setScope(null);

                return base_path($path);
            }
        }

        return parent::getMigrationPath();
    }
}