<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\UninstallDroplet;

class DropletUninstallCommand extends Command
{
    protected $signature = 'droplet:uninstall {droplet}';

    public function handle()
    {
        $slug = $this->argument('droplet');
        $this->call('migrate:reset', ['--droplet' => $slug]);

        if ($this->serve(new UninstallDroplet($slug))) {
            $this->info('The ['.$slug.'] droplet successfully uninstalled.');
        } else {
            $this->error('Droplet could not be uninstalled');
        }
    }
}