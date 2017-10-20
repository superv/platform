<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\InstallDroplet;

class DropletInstallCommand extends Command
{
    protected $signature = 'droplet:install {droplet} {--path=} {--seed}';

    public function handle()
    {
        $slug = $this->argument('droplet');
        $path = $this->option('path');
        if ($this->serve(new InstallDroplet($slug, $path))) {
            $this->call('migrate', ['--droplet' => $slug]);
            $this->info('The ['.$slug.'] droplet was installed.');

            if ($this->option('seed')) {
                $this->call('droplet:seed', ['droplet' => $slug]);
            }
        } else {
            $this->error('Droplet could not be installed');
        }
    }
}
