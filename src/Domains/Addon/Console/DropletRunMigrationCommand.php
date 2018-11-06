<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\AddonModel;

class DropletRunMigrationCommand extends Command
{
    protected $signature = 'droplet:migrate {--droplet=}';

    public function handle()
    {
        if (! $droplet = $this->option('droplet')) {
            $droplet = $this->choice('Droplet ?', AddonModel::enabled()->latest()->get()->pluck('slug')->all());
        }

        $this->call('migrate', ['--scope' => $droplet]);
    }
}