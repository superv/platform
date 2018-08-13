<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\DropletModel;

class DropletRunMigrationCommand extends Command
{
    protected $signature = 'droplet:migrate {--droplet=}';

    public function handle()
    {
        if (! $droplet = $this->option('droplet')) {
            $droplet = $this->choice('Droplet ?', DropletModel::enabled()->latest()->get()->pluck('slug')->all());
        }

        $this->call('migrate', ['--scope' => $droplet]);
    }
}