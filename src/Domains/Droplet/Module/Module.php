<?php

namespace SuperV\Platform\Domains\Droplet\Module;

use Illuminate\Contracts\Console\Kernel;
use SuperV\Platform\Domains\Droplet\Droplet;

class Module extends Droplet
{
    protected $installs = [];

    public function onInstalled()
    {
        foreach ($this->installs as $droplet => $path) {
            app(Kernel::class)->call('droplet:install', [
                'droplet' => $droplet,
                '--path'  => $this->getPath($path),
            ]);
        }
    }
}
