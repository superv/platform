<?php namespace SuperV\Platform\Domains\Droplet\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Droplet\Feature\InstallDropletFeature;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;

class DropletInstall extends Command
{
    use ServesFeaturesTrait;
    
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