<?php

namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Platform\Domains\Console\Jobs\ConfigureMigrator;
use SuperV\Platform\Domains\Database\Migration\Migrator;

class MigrateCommand extends \Illuminate\Database\Console\Migrations\MigrateCommand
{
    use DispatchesJobs;

    protected $signature = 'migrate {--database= : The database connection to use.}
                {--force : Force the operation to run when in production.}
                {--path= : The path of migrations files to be executed.}
                {--pretend : Dump the SQL queries that would be run.}
                {--seed : Indicates if the seed task should be re-run.}
                {--step : Force the migrations to be run so they can be rolled back individually.}
                {--droplet= : The droplet slug to migrate.}';

    /** @var  Migrator */
    protected $migrator;

    public function handle()
    {
        $this->dispatch(new ConfigureMigrator($this->migrator, $this->option('droplet'), $this->input));

        parent::handle();

        if ($this->migrator->hasDroplet()) {
             if ($this->option('seed')) {
                 app(Kernel::class)->call('droplet:seed', ['droplet' => $this->option('droplet')]);
             }
         }
    }
}
