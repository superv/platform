<?php

namespace SuperV\Platform\Packs\Droplet\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Packs\Droplet\Installer;

class DropletInstallCommand extends Command
{
    protected $signature = 'droplet:install {slug} {--path=}';

    public function handle(Installer $installer)
    {
        $installer->path($this->option('path'))
                  ->slug($this->argument('slug'))
                  ->install();
    }
}