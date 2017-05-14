<?php namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\MakeDropletFeature;

class MakeDroplet extends Command
{
    protected $signature = 'make:droplet {slug}';
    
    public function handle()
    {
        $this->serve(new MakeDropletFeature($this->argument('slug')));
        
        $this->info('The [' . $this->argument('slug') . '] droplet was created.');
    }
}