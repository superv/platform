<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\SeedDroplet;

class DropletSeedCommand extends Command
{
    protected $signature = 'droplet:seed {droplet}';

    public function handle()
    {
        $slug = $this->argument('droplet');

        $this->serve(new SeedDroplet($slug));

        $this->comment('Seeding complete');
    }
}