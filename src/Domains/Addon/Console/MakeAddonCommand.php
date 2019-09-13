<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\Features\MakeAddon;

class MakeAddonCommand extends Command
{
    protected $signature = 'make:addon {identifier} {--type=module} {--path=}';

    public function handle()
    {
        $identifier = $this->argument('identifier');

        MakeAddon::dispatch(
            $identifier,
            $this->option('type'),
            $this->option('path')
        );

        $this->info('The ['.$identifier.'] addon was created.');
    }
}
