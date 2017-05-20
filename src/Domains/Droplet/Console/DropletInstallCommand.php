<?php namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\InstallDropletFeature;

class DropletInstallCommand extends Command
{
    protected $signature = 'droplet:install {slug}';
    
    public function handle()
    {
        if ($this->serve(new InstallDropletFeature($this->argument('slug')))) {
            $this->info('The [' . $this->argument('slug') . '] droplet was installed.');
        } else {
            $this->error('Droplet could not be installed');
        }
    }
}