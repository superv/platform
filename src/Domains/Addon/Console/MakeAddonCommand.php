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
        $identifier = $this->argument('identifier');

        if (! preg_match('/^([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)$/', $identifier)) {
            throw new \Exception('Identifier should be in this format: {vendor}.{addon}: '.$identifier);
        }
        $request = new MakeAddonRequest(
            $identifier,
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
