<?php namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\InstallDropletFeature;

class DropletInstallCommand extends Command
{
    protected $signature = 'droplet:install {slug} {--path= :}';
    
    public function handle()
    {
        $slug = $this->argument('slug');
        $path = $this->option('path');
        if ($this->serve(new InstallDropletFeature($slug, $path))) {
            $this->info('The [' . $slug . '] droplet was installed.');
        } else {
            $this->error('Droplet could not be installed');
        }
    }
}