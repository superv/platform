<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\Features\MakeAddon;

class MakeAddonCommand extends Command
{
    protected $signature = 'make:addon {slug} {--path=}';

    public function handle()
    {
        $slug = $this->argument('slug');
        $path = $this->option('path');
        $this->dispatch(new MakeAddon($slug, $path));

        $this->info('The ['.$slug.'] addon was created.');
    }
}