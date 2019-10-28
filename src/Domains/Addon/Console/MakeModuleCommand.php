<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {identifier} {--path=} {--force}';

    public function handle()
    {
        $this->call('make:addon', [
            'identifier' => $this->argument('identifier'),
            '--type'     => 'module',
            '--path'     => $this->option('path'),
            '--force'    => $this->option('force'),
        ]);
    }
}
