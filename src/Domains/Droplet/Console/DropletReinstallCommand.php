<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class DropletReinstallCommand extends Command
{
    protected $signature = 'droplet:reinstall {droplet} {--seed}';

    public function handle(Droplets $droplets)
    {
        $slug = $this->argument('droplet');

        /** @var DropletModel $droplet */
        abort_unless($droplet = $droplets->withSlug($slug), 404);

        $this->call('droplet:uninstall', ['droplet' => $droplet->getSlug()]);

        $this->call('droplet:install', [
            'droplet' => $droplet->getSlug(),
            '--path'  => $droplet->getPath(),
            '--seed'  => $this->option('seed'),
        ]);
    }
}
