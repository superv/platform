<?php

namespace SuperV\Platform\Domains\Addon\Console;

use SuperV\Platform\Contracts\Command;

class MakePanelCommand extends Command
{
    protected $signature = 'make:panel {identifier} {--path=} {--force}';

    public function handle()
    {
        $this->call('make:addon', [
            'identifier' => $this->argument('identifier'),
            '--type'     => 'panel',
            '--path'     => $this->option('path'),
            '--force'    => $this->option('force'),
        ]);
    }
}
