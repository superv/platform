<?php

namespace SuperV\Platform\Domains\Database\Migration\Console;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputOption;

class RefreshCommand extends \Illuminate\Database\Console\Migrations\RefreshCommand
{
    use DispatchesJobs;

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $path = $this->input->getOption('path');
        $droplet = $this->input->getOption('droplet');
        $force = $this->input->getOption('force');
        $database = $this->input->getOption('database');

        // If the "step" option is specified it means we only want to rollback a small
        // number of migrations before migrating again. For example, the user might
        // only rollback and remigrate the latest four migrations instead of all.
        $step = $this->input->getOption('step') ?: 0;

        if ($step > 0) {
            $this->call(
                'migrate:rollback',
                [
                    '--database' => $database,
                    '--droplet'  => $droplet,
                    '--force'    => $force,
                    '--step'     => $step,
                ]
            );
        } else {
            $this->call(
                'migrate:reset',
                [
                    '--database' => $database,
                    '--droplet'  => $droplet,
                    '--force'    => $force,
                ]
            );
        }

        // The refresh command is essentially just a brief aggregate of a few other of
        // the migration commands and just provides a convenient wrapper to execute
        // them in succession. We'll also see if we need to re-seed the database.
        $this->call(
            'migrate',
            [
                '--database' => $database,
                '--droplet'  => $droplet,
                '--force'    => $force,
                '--path'     => $path,
                '--seed'     => $this->input->getOption('seed'),
            ]
        );

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }
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
                ['droplet', null, InputOption::VALUE_OPTIONAL, 'The droplet to reset migrations.'],
            ]
        );
    }
}
