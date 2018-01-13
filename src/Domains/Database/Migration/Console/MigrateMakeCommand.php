<?php

namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Console\Jobs\ConfigureCreator;
use SuperV\Platform\Domains\Database\Migration\MigrationCreator;

class MigrateMakeCommand extends \Illuminate\Database\Console\Migrations\MigrateMakeCommand
{
    use DispatchesJobs;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:migration {name : The name of the migration.}
        {--table= : The table to migrate.}
        {--create= : The table to be created.}
        {--fields : Create a fields migration.}
        {--droplet= : The droplet to create a migration for.}
        {--path= : The location where the migration file should be created.}
        {--platform}';

    /**
     * The migration creator.
     *
     * @var MigrationCreator
     */
    protected $creator;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('platform')) {
            $this->input->setArgument('name', 'platform__'.$this->input->getArgument('name'));

            $this->input->setOption('path', platform_path('database/migrations'));
        } else {
            $this->dispatch(new ConfigureCreator($this->option('droplet'), $this->input, $this->creator));
        }

        $this->creator->setInput($this->input);

        parent::handle();
    }
}