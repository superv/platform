<?php

namespace SuperV\Platform\Domains\Application\Console;

use Illuminate\Console\Command;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class Install extends Command
{
    protected $signature = 'superv:install';

    protected $description = '';

    public function handle(Droplets $droplets)
    {
        $this->call('migrate');

        $this->call('migrate', ['--path' => 'vendor/superv/platform/database/migrations']);

        $droplets->create([
            'id'        => 1,
            'name'      => 'platform',
            'vendor'    => 'superv',
            'slug'      => 'superv.platform',
            'namespace' => 'SuperV\Platform',
            'path'      => 'vendor/superv/platform',
            'type'      => 'module',
            'enabled'   => false,
        ]);

        $this->call('env:set', ['line' => 'SUPERV_INSTALLED=true']);

        $this->call('droplet:install', [
            'slug'   => 'superv.modules.auth',
            '--path' => 'droplets/superv/modules/auth',
        ]);

        $this->call('droplet:install', [
            'slug'   => 'superv.modules.supreme',
            '--path' => 'droplets/superv/modules/supreme',
        ]);
        $this->call('droplet:install', [
            'slug'   => 'superv.modules.hosting',
            '--path' => 'droplets/superv/modules/hosting',
        ]);
    }
}