<?php

namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Console\Kernel;
use SuperV\Platform\Domains\Console\Jobs\ConfigureMigrator;
use SuperV\Platform\Domains\Database\Migration\Migrator;
use Symfony\Component\Console\Input\InputOption;

class ResetCommand extends \Illuminate\Database\Console\Migrations\ResetCommand
{
    use DispatchesJobs;

    /** @var  Migrator */
    protected $migrator;

    public function handle()
    {
        $this->dispatch(new ConfigureMigrator($this->migrator, $this->option('droplet'), $this->input));

//        if ($droplet = $this->migrator->getDroplet()) {
//            if ($this->option('seed')) {
//                app(Kernel::class)->call('droplet:seed', ['droplet' => $droplet->getSlug()]);
//            }
//        }

        parent::handle();
    }

    /**
     * Get the command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['droplet', null, InputOption::VALUE_OPTIONAL, 'The droplet to reset migrations for.'],
                ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
            ]
        );
    }
}
