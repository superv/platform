<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;
use SuperV\Platform\Domains\Addon\Features\MakeAddon;
use SuperV\Platform\Domains\Addon\Features\MakeAddonRequest;

class MakePanelCommand extends Command
{
    protected $signature = 'make:panel {identifier} {--path=} {--force}';

    public function handle()
    {
        $request = new MakeAddonRequest(
            $this->argument('identifier'),
            'panel'
        );

        if ($this->hasOption('path')) {
            $request->setTargetPath($this->option('path'));
        }

        MakeAddon::dispatch($request, $this->option('force'));

        $this->info('The ['.$this->argument('identifier').'] panel was created.');
    }
}
