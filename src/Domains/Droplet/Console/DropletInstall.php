<?php namespace SuperV\Platform\Domains\Droplet\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Droplet\DropletManager;

class DropletInstall extends Command
{
    protected $signature = 'droplet:install {namespace}';
    
    public function handle(DropletManager $droplets)
    {
        if ($droplets->install($this->argument('namespace'))) {
            $this->info('The [' . $this->argument('namespace') . '] droplet was installed.');
        } else {
            $this->error('Droplet could not be installed');
        }
    }
}