<?php

namespace SuperV\Platform\Domains\Droplet\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Droplet\Feature\MakeDroplet;

class MakeDropletCommand extends Command
{
    protected $signature = 'make:droplet {slug} {--path=}';

    public function handle()
    {
        $slug = $this->argument('slug');
        $path = $this->option('path');
        $this->serve(new MakeDroplet($slug, $path));

        $this->info('The ['.$slug.'] droplet was created.');
    }
}
