<?php

namespace SuperV\Platform\Packs\Database\Migrations\Console;

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

    /** @var \SuperV\Platform\Packs\Database\Migrations\MigrationCreator */
    protected $creator;

    public function handle()
    {
        if ($this->option('scope')) {
            $this->creator->setScope($this->option('scope'));
        }
        parent::handle();
    }
}