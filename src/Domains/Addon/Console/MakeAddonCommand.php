<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\Features\MakeAddon;
use SuperV\Platform\Domains\Addon\Features\MakeAddonRequest;

class MakeAddonCommand extends Command
{
    protected $signature = 'make:addon {vendor} {package} {--type=module} {--path=} {--force}';

    public function handle()
    {
        $request = new MakeAddonRequest(
            $this->argument('vendor'),
            $this->argument('package'),
            $this->option('type')
        );

        if ($this->hasOption('identifier')) {
            $request->setIdentifier($this->option('identifier'));
        }

        if ($this->hasOption('path')) {
            $request->setTargetPath($this->option('path'));
        }

        $identifier = MakeAddon::dispatch($request, $this->option('force'));

        $this->info('The ['.$identifier.'] addon was created.');
    }
}
