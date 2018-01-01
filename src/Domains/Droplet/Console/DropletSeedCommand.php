<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\SeedDroplet;

class DropletSeedCommand extends Command
{
    protected $signature = 'droplet:seed {droplet}';

    public function handle()
    {
        $this->serve(new SeedDroplet($this->argument('droplet')));

        $this->comment('Seeding complete');
    }
}