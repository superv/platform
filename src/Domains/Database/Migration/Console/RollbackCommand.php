<?php

namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Console\Jobs\ConfigureMigrator;
use SuperV\Platform\Domains\Database\Migration\Migrator;
use Symfony\Component\Console\Input\InputOption;

class RollbackCommand extends \Illuminate\Database\Console\Migrations\RollbackCommand
{
    use DispatchesJobs;

    /** @var  Migrator */
    protected $migrator;

    public function handle()
    {
        $this->dispatch(new ConfigureMigrator($this->migrator, $this->option('droplet'), $this->input));

        parent::handle();
    }

    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['droplet', null, InputOption::VALUE_OPTIONAL, 'The droplet to rollback for.'],
            ]
        );
    }
}