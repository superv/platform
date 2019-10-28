<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;

class MakeDropCommand extends Command
{
    protected $signature = 'make:drop {identifier} {--path=} {--force}';

    public function handle()
    {
        $this->call('make:addon', [
            'identifier' => $this->argument('identifier'),
            '--type'     => 'drop',
            '--path'     => $this->option('path'),
            '--force'    => $this->option('force'),
        ]);
    }
}
