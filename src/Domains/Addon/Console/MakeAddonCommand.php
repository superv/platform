<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\Features\MakeAddon;
use SuperV\Platform\Domains\Addon\Features\MakeAddonRequest;

class MakeAddonCommand extends Command
{
    protected $signature = 'make:addon {identifier} {--type=module} {--path=} {--force}';

    public function handle()
    {
        $request = new MakeAddonRequest(
            $this->argument('identifier'),
            $this->option('type')
        );

        if ($this->hasOption('path')) {
            $request->setTargetPath($this->option('path'));
        }

        MakeAddon::dispatch($request, $this->option('force'));

        $this->info('The ['.$this->argument('identifier').'] addon was created.');
    }
}
